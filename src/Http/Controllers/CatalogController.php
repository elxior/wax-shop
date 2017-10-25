<?php

namespace App\Shop\Http\Controllers;

use Illuminate\Routing\Controller;
use Wax\Core\Contracts\FilterableRepositoryContract;

class CatalogController extends Controller
{
    protected $repo;

    public function __construct(FilterableRepositoryContract $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $featured = $this->repo->with(['category.products'])->getAll();
        $nonFeatured = $this->repo->with(['category.products'])->getForIndex($featured->toArray());
        $categories = $featured->merge($nonFeatured)->pluck('category')->unique()->filter();

        return view('modules.Shop.pages.shop', [
            'featuredProducts' => $featured->toArray(),
            'productsByCategory' => $nonFeatured->toArray(),
            'categories' => $categories->toArray()
        ]);
    }

    public function show($slug)
    {
        $product = $this->repo->with(['relatedProducts'])->getBySlug($slug);

        return view('modules.Shop.pages.product-detail', [
            'product' => $product->toArray(),
            'relatedProducts' => $product->relatedProducts->toArray(),
        ]);
    }
}
