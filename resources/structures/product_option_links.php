<?php

use Illuminate\Support\Facades\DB;

    $structure = array(
        'table' => 'product_option_links',
        'primary_key' => 'id',
        'fields' => array (),
        'settings' => array(
            'page_title' => "Shop - Product Option Links",
            'privilege' => 'Shop - Products', // all product related structures should share this privilege
            'create_new_text' => 'Create Product Option Link',
            'back_link' => 'index.php',
            'back_link_text' => 'main menu',
            'editor_subtitle' => 'Product Options',
            'list_fields' => array('option_id', 'values', 'required'),
            'order' => array('option_id'),
            'search_fields' => array('product_id', 'option_id'),
        ),
        'predelete' => function ($id, &$cms) {
            $shop = app()->make('Wax\Shop\Services\ShopAdminService');
            return $shop->deleteProductOptionLink($id);
        },
        'presave' => function ($id, &$cms) {
            if ((int)$id == 0) {
                return true;
            }
            // if the option changed (as opposed to a new record being created), cleanup the old (now invalid) modifiers.
            // Appropriate new modifiers may be created in post save.

            $oldLinkId = DB::table('product_option_links')
                ->where('id', $id)
                ->value('option_id');

            if (!$oldLinkId) {
                return true;
            }

            $shop = app()->make('Wax\Shop\Services\ShopAdminService');
            return $shop->addProductOptionLink($oldLinkId);
        },
        'postsave' => function ($id, &$cms) {
            $shop = app()->make('Wax\Shop\Services\ShopAdminService');
            return $shop->addProductOptionLink($id);
        }
    );

    $structure['fields'][] = array(
        'name' => 'product_id',
        'display_name' => 'Product',
        'type' => 'hidden',
        'bind' => CMS_BIND_ONE_TO_MANY,
        'bind_table' => 'products',
        'bind_key' => 'id',
        'bind_value' => 'id',
    );

    $structure['fields'][] = array(
        'group' => 'Option',
        'name' => 'option_id',
        'display_name' => 'Option',
        'type' => 'select',
        'bind' => CMS_BIND_ONE_TO_MANY,
        'bind_table' => 'product_options',
        'bind_key' => 'id',
        'bind_value' => 'name',
    );

    $structure['fields'][] = array(
        'group' => 'Option',
        'name' => 'required',
        'display_name' => 'Required',
        'type' => 'boolean',
        'default' => 1
    );

    $structure['fields'][] = array(
        'name' => 'values',
        'display_name' => 'Values',
        'type' => 'custom',

        /**
         * custom callback for Cms->getRecords & Cms->getRecord
         */
        'getRecords' => function (&$cms, $record, $resolveBoundValues) {
            $optionId = $this->currentItem['option_id_key'] ?? $this->currentItem['option_id'];
            $productId = $this->currentItem['product_id_key'] ?? $this->currentItem['product_id'];

            return DB::table('product_option_values as pov')
                ->join('product_option_value_links as povl', 'povl.value_id', '=', 'pov.id')
                ->where('pov.option_id', $optionId)
                ->where('povl.product_id', $productId)
                ->pluck('name');
        },
        'getRecord' => function () {
            $optionId = $this->currentItem['option_id_key'] ?? $this->currentItem['option_id'];
            $productId = $this->currentItem['product_id_key'] ?? $this->currentItem['product_id'];

            return DB::table('product_option_values as pov')
                ->join('product_option_value_links as povl', 'povl.value_id', '=', 'pov.id')
                ->where('pov.option_id', $optionId)
                ->where('povl.product_id', $productId)
                ->pluck('name');
        },

        'render' => function () {
            $parentId = (int)request('parentId');
            $parentStructure = request('parentStructure');
            $parentField = request('parentField');
            $guid = request('guid');
            $output = <<< VALUESOUT

			<script type='text/javascript'>

				$("#option_id").change(callLoadValues);
				$(callLoadValues);

				function callLoadValues() {
					$("#option_values").empty();
					var data = {
						parentStructure: '{$parentStructure}',
						parentField: '{$parentField}',
						structure: '{$this->structure['settings']['structure']}',
						action: 'field_method',
						field: 'values',
						method: 'loadValues',
						parentId: {$parentId},
						id: {$this->currentItem[$this->structure['primary_key']]},
						guid: '$guid',
						option: $("#option_id option:selected").val()

					};
					$.post("/admin/cms/{$this->structure['settings']['structure']}/edit/{$this->currentItem[$this->structure['primary_key']]}", data, handleLoadValues, 'json');
				}

				function handleLoadValues(result) {
					var html = '';
					for( i in result) {
						html += '<input type="checkbox" name="values[]" value="' + result[i].id + '"' + (result[i].enabled > 0 ? ' checked' : '' ) + '> ' + result[i].name + '<br />';
					}
					$("#option_values").append(html);
				}

			</script>

			<div id="option_values"></div>

VALUESOUT;
            return $output;
        },

        'loadValues' => function () {
            $result = [];
            if ((int)request('option') > 0) {
                $checkedSubquery = DB::table('product_option_value_links as vl')
                    ->selectRaw('count(vl.id) > 0')
                    ->where('vl.product_id', request('parentId'))
                    ->whereRaw('vl.value_id = v.id');

                $result = DB::table('product_option_values as v')
                    ->select('v.id', 'v.name')
                    ->selectSub($checkedSubquery, 'enabled')
                    ->where('v.option_id', request('option'))
                    ->get();
            }
            echo json_encode($result);
            exit;
        },

        // custom-field presaves are called before structure-wide presaves
        'presave' => function ($id, &$cms) {

            $values = array_map('intval', request('values'));
            $productRef = $cms->data->varRef('product_id');

            $cms->data->query("delete from product_option_value_links where product_id={$productRef} and value_id in (select distinct id from product_option_values where option_id=" . (int)request('option_id') . ") and value_id not in (" . implode(',', array_merge(array(0), $values)) . ")");

            foreach ($values as $value) {
                $cms->data->query("insert ignore into product_option_value_links (product_id, value_id) values ($productRef, $value)");
            }

            return true;
        }
    );
