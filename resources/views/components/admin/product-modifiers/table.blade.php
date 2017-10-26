<table class="table" width="100%">
    @include('shop::components.admin.product-modifiers.table-head', ['options' => $options])

    @foreach ($options as $option)
        <tr>
            @foreach ($option->values as $value)
                <td>{{ $value->value_name }}</td>
            @endforeach
            <td><input type="text" style="width: 100%" name="{{ $option->id }}[sku]" value="{{ $option->sku }}"></td>
            <td><input type="text" style="width: 100%" name="{{ $option->id }}[price]" value="{{ $option->price }}"></td>
            <td><input type="text" style="width: 100%" name="{{ $option->id }}[weight]" value="{{ $option->weight }}"></td>
            <td><input type="text" style="width: 100%" name="{{ $option->id }}[inventory]" value="{{ $option->inventory }}"></td>
            <td style="white-space:nowrap"><input type="checkbox" name="{{ $option->id }}[disable]" value="1" {{ $option->disable ? 'CHECKED' : '' }} > Disable</td>
        </tr>
    @endforeach
</table>
