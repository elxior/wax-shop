<?php

namespace App\Shop\Repositories;

use Illuminate\Database\Eloquent\Model;
use Wax\Core\Repositories\BaseFilterableRepository;
use Wax\Core\Contracts\FilterableRepositoryContract;

class ProductRepository extends BaseFilterableRepository
{
    public function getFeatured()
    {
        return $this->getQuery()->featured()->take(2)->get();
    }

    public function getForIndex(array $excludeAsFeatured = [])
    {
        $results = $this
            ->getQuery()
            ->when($excludeAsFeatured, function ($builder) use ($excludeAsFeatured) {
                return $builder->whereNotIn('id', array_map(function ($model) {
                    return $model['id'];
                }, $excludeAsFeatured));
            })
            ->get();

        return $this->filterResults($results);
    }
}
