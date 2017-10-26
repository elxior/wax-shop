<?php

namespace Wax\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Wax\Shop\Models\Product;
use Wax\Shop\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

use Wax\Db;

class ProductApiController extends Controller
{
    protected $repo;

    public function __construct(ProductRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $products = $this->repo->getHomepageProducts();

        dd($products->makeEntities());
        return response()->json();
    }

    public function show(Product $product)
    {
        dd($product->attrs->toArray());
        return response()->json($product->load('files')->makeEntities());
    }
}
