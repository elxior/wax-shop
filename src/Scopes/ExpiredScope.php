<?php

namespace App\Shop\Scopes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ExpiredScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('expired_at', '>', Carbon::now())
            ->orWhereNull('expired_at');
    }
}