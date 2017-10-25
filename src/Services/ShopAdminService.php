<?php

namespace App\Shop\Services;

use App\Shop\Models\Product\Category;
use Illuminate\Support\Facades\DB;

class ShopAdminService
{
    public static function updateCategoryBreadcrumbs()
    {
        Category::all()->each->regenerateBreadcrumbs();
    }

    /**
     * Doesn't actually delete a link!  Clean up the option modifiers after removing an option.  Since modifiers are
     * tied to a specific combination of options, changing options invalidates any existing modifiers.  This process
     * adapts the existing mods to fit the new set of options.
     *
     * @param int $id
     * @return bool
     */
    public function deleteProductOptionLink($id)
    {
        // get all the values belonging to this link's option
        $values = DB::table('product_option_values as pov')
            ->join('product_option_links as pol', 'pov.option_id', '=', 'pol.option_id')
            ->join('product_option_value_links as povl', 'pov.id', '=', 'povl.value_id')
            ->whereColumn('povl.product_id', 'pol.product_id')
            ->where('pol.id', $id)
            ->orderBy('pov.id')
            ->pluck('pov.id')
            ->unique();

        // if there's no values then there's nothing to do.
        if (!count($values)) {
            return true;
        }

        // get all the modifiers belonging to this link's product
        $ms = DB::table('product_option_links as pol')
            ->select('pom.*')
            ->join('product_option_modifiers as pom', 'pom.product_id', '=', 'pol.product_id')
            ->whereColumn('pom.product_id', 'pol.product_id')
            ->where('pol.id', $id)
            ->get();

        $mods = [];
        foreach ($ms as $m) {
            $m['values'] = preg_replace('/\b('.implode('|', $values).')\b/', '', $m['values']);
            $m['values'] = trim(str_replace('--', '-', $m['values']), '-');

            unset($m['id']);
            if (!isset($mods[$m['values']])) {
                $mods[$m['values']] = $m;
            }
        }

        foreach ($mods as $mod) {
            $exists = DB::table('product_option_modifiers')
                ->where('product_id', $mod['product_id'])
                ->where('values', $mod['values'])
                ->exists();

            if (!$exists) {
                DB::table('product_option_modifiers')
                    ->insert($mod);
            }
        }

        // invalid modifiers get cleaned up after saving from the modifiers editor, so no point going
        // out of the way to do it here.  Also if you change your mind and re-add an option before cleanup
        // happens, your old modifiers come back.

        return true;
    }

    /**
     * Doesn't actually create a link!  Clean up the option modifiers after adding an option.  Since modifiers are
     * tied to a specific combination of options, changing options invalidates any existing modifiers.  This process
     * adapts the existing mods to fit the new set of options.
     *
     * @param int $id
     * @return bool
     */
    public function addProductOptionLink($id)
    {
        // get all the values belonging to this link's option
        $values = DB::table('product_option_values as pov')
            ->join('product_option_links as pol', 'pov.option_id', '=', 'pol.option_id')
            ->join('product_option_value_links as povl', 'pov.id', '=', 'povl.value_id')
            ->whereColumn('povl.product_id', 'pol.product_id')
            ->where('pol.id', $id)
            ->orderBy('pov.id')
            ->pluck('pov.id')
            ->unique();

        // if there's no values then there's nothing to do.
        if (!count($values)) {
            return true;
        }

        // get all the modifiers belonging to this link's product
        $ms = DB::table('product_option_links as pol')
            ->select('pom.*')
            ->join('product_option_modifiers as pom', 'pom.product_id', '=', 'pol.product_id')
            ->whereColumn('pom.product_id', 'pol.product_id')
            ->where('pol.id', $id)
            ->get();

        $mods = [];
        foreach ($ms as $m) {
            if (!preg_match('/\b('.implode('|', $values->toArray()).')\b/', $m->values)) {
                foreach ($values as $i => $value) {
                    $newM = (array)$m; // copy the mod instead of reusing the original so we can edit in place
                    $opts = explode('-', $newM['values']);
                    $opts[] = $value;
                    sort($opts);
                    $newM['values'] = implode('-', $opts);

                    if ($i > 0) {
                        $newM['sku'] .= "-{$i}";
                    }
                    unset($newM['id']);
                    if (!isset($mods[$newM['values']])) {
                        $mods[$newM['values']] = $newM;
                    }
                }
            }
        };

        foreach ($mods as $mod) {
            $exists = DB::table('product_option_modifiers')
                ->where('product_id', $mod['product_id'])
                ->where('values', $mod['values'])
                ->exists();

            if (!$exists) {
                DB::table('product_option_modifiers')
                    ->insert($mod);
            }
        }

        // invalid modifiers get cleaned up after saving from the modifiers editor, so no point going
        // out of the way to do it here.  Also if you change your mind and re-add an option before cleanup
        // happens, your old modifiers come back.

        return true;
    }
}
