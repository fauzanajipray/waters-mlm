{{-- Include widget wrapper --}}
@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_start')

{{-- Define your widget --}}

<style>
    .card-header {
        font-weight: bold;
    }
</style>
<div class="card">
    <table id="dataTable" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Product</th>
                <th>Stock Now</th>
                <th>Minimum</th>
            </tr>
        </thead>
        <tbody>
            @if ($data['products']->count() == 0)
                <tr>
                    <td colspan="3" class="text-center">No matching entries found</td>
                </tr>
            @endif
            @foreach ($data['products'] as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>{{ $product->min_stock_pusat}}</td>
                </tr>
            @endforeach
        </tbody>
        <tfooter>
            <tr>
                <td colspan="3">
                    <div class="float-left">
                        Show {{ $data['products']->count() }} from {{ $data['total'] }}
                    </div>
                    <div class="float-right">
                        <a href="{{ url('product-below-stock') }}" class="text-primary">More about Product
                        </a>
                    </div>

                </td>
            </tr>
        </tfooter>
    </table>
</div>

@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_end')


{{-- ajax call --}}
@push('after_scripts')
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable();
        });
    </script>

@endpush
