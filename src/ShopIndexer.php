<?php

namespace Wax\Shop;

use Wax\Core\Contracts\FilterableRepositoryContract;
use Wax\SiteSearch\Contracts\IndexerContract;
use Wax\SiteSearch\IndexingCoordinator;

/**
 *
 */
class ShopIndexer implements IndexerContract
{
    public $ob;
    protected $indexer;
    protected $repo;

    public function __construct(IndexingCoordinator $indexer, FilterableRepositoryContract $repo)
    {
        $this->indexer = $indexer;

        $this->repo = $repo;
    }

    public function crawl()
    {
        $this->repo->getAll()
            ->each(function ($product) {
                $this->processProduct($product);
            });
    }

    protected function processProduct($product)
    {
        $catStr = $product->category->name ?? '';

        $attrStr = $product->attrs
            ->map(function ($attr) {
                return "{$attr->name}: {$attr->value}";
            })->implode(', ');

        $page = array(
            'module' => 'shop',
            'url' => route('shop::productDetail', ['slug' => $product->url_slug], false),
            'title' => $product->name,
            'content' => "{$product->sku} {$product->name} {$catStr} {$attrStr} {$product->description}",
            'description' => $product->short_description,
        );

        $this->indexer->processPage($page);
    }
}
