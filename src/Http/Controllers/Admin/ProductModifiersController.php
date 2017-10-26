<?php

namespace Wax\Shop\Http\Controllers\Admin;

use Wax\Shop\Models\Product;
use Wax\Shop\Models\Product\OptionModifier;
use Wax\Shop\Services\ShopService;
use App\Wax\Admin\Cms\CmsAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;

class ProductModifiersController
{
    protected $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    public function show(Request $request, Product $product)
    {
        $prodCms = new CmsAdmin('products');
        if (!Auth::check() || !Auth::user()->hasPrivilege($prodCms->structure['settings']['privilege'])) {
            throw new UnauthorizedException('Insufficient permission.');
        }

        return view(
            'shop::pages.admin.product-modifiers',
            [
                'pageTitle' => 'Shop - Product Modifiers',
                'product' => $product,
                'options' => $this->prepareOptions($product, $request)
            ]
        );
    }

    public function update(Request $request, Product $product)
    {
        $prodCms = new CmsAdmin('products');
        if (!Auth::check() || !Auth::user()->hasPrivilege($prodCms->structure['settings']['privilege'])) {
            throw new UnauthorizedException('Insufficient permission.');
        }

        if ($request->input('action') === 'Cancel') {
            return $this->buildBackToProductEditorResponse($prodCms, $product);
        }

        $options = $this->prepareOptions($product, $request);

        foreach ($options as $option) {
            $this->saveModifier($product->id, $option);
        }

        OptionModifier::where('product_id', $product->id)
            ->whereNotIn('values', $options->pluck('id'))
            ->delete();

        return $this->buildBackToProductEditorResponse($prodCms, $product);
    }

    protected function buildBackToProductEditorResponse($cms, $product)
    {
        return response()->redirectToRoute($cms->structure['settings']['edit_route'], [
            'structure' => $cms->structure['settings']['structure'],
            'id' => $product->id,
        ]);
    }

    protected function saveModifier($productId, $option)
    {
        $mod = OptionModifier::firstOrNew([
            'product_id' => $productId,
            'values' => $option->id,
        ]);

        $mod->sku = $option->sku ?: null;
        $mod->price = $option->price ?: null;
        $mod->inventory = $option->inventory ?: null;
        $mod->weight = $option->weight ?: null;
        $mod->disable = (int)$option->disable;

        $mod->save();
    }

    /**
     * Convert raw 'values' collection to an object with the 'id' pre-generated and empty values cleaned.
     *
     * @param Product $product
     * @param Request $request
     * @return Collection
     */
    protected function prepareOptions($product, $request)
    {
        return $product->getOptionPermutations()->filter(function ($item) {
            // Reject permutations where all options are optional and have no value set, meaning its the base product
            return $item->sum('value_id');
        })->map(function ($option) use ($product, $request) {
            $id = implode('-', $option->pluck('value_id')->sort()->toArray());

            $modifier = $product->optionModifiers->where('values', $id)->first();

            return (object)[
                'id' => $id,
                'sku' => $request->input("{$id}.sku", $modifier->sku ?? ''),
                'price' => $request->input("{$id}.price", $modifier->price ?? ''),
                'inventory' => $request->input("{$id}.inventory", $modifier->inventory ?? ''),
                'weight' => $request->input("{$id}.weight", $modifier->weight ?? ''),
                'disable' => (bool)$request->input("{$id}.disable", $modifier->disable ?? false),
                'values' => $option
            ];
        });
    }
}
