<?php 

namespace App\Http\Controllers\HelferinTraits;

use App\Helpers\Exports\ArrayExport;
use App\Helpers\Tools;
use App\Models\Configuration;
use App\Models\Context;
use App\Models\Customer;
use App\Models\Product;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

trait ReportsProductSalesTrait
{

    /**
     * Product Sales Report.
     *
     * @return Spread Sheet download
     */
    public function reportProductSales(Request $request)
    {
        // abi_r(Carbon::now()->month);die();

        $product_sales_month_from = $request->input('product_sales_month_from', 1);
        
        $product_sales_month_to   = $request->input('product_sales_month_to', Carbon::now()->month );

        $nbr_years = $request->input('product_sales_years_to_compare', 1);

        $document_total_tax = $request->input('product_sales_value', 'total_tax_incl');

        if ( !in_array($document_total_tax, ['total_tax_incl', 'total_tax_excl']) )
            $document_total_tax = 'total_tax_incl';
 
        $customer_id = $request->input('product_sales_customer_id', 0);

        $model = $request->input('product_sales_model', Configuration::get('RECENT_SALES_CLASS'));

        // calculate dates
        // http://zetcode.com/php/carbon/
        $years = [];
        // Current year
        $current_year = Carbon::now()->year;
        $first_year = Carbon::now()->year - $nbr_years;
        $month_from = $product_sales_month_from;
        $month_to   = $product_sales_month_to;
        $date_from = Carbon::create($first_year, $month_from, 1)->startOfDay();
        $date_to   = Carbon::create($first_year, $month_to  , 1)->endOfMonth()->endOfDay();

        $list_of_years = [];
        for ($i=0; $i <= $nbr_years; $i++) { 
            # code...
            $list_of_years[] = $first_year + $i;
        }

        // abi_r($list_of_years, true);

        // abi_r($date_from);
        // abi_r($date_to, true);


        $models = $this->models;
        if ( !in_array($model, $models) )
            $model = Configuration::get('RECENT_SALES_CLASS');
        $class = '\\App\\Models\\'.$model.'Line';
        $table = \Str::snake(\Str::plural($model));
        $route = str_replace('_', '', $table);

        $selectorMonthList = Tools::selectorMonthList();

        $document_reference_date = 'close_date';        // 'document_date'
        $document_reference_date = 'document_date';        // 'document_date'

        $is_invoiceable_flag = ($model == 'CustomerShippingSlip') ? true : false;

        // Wanna dance, Honey Bunny?

        
        // All Products. Lets see:
        $products = Product::select('id', 'name', 'reference')  // , 'measure_unit_id')
//                            ->with('measureunit')
                            ->orderBy('reference', 'asc')
//                            ->take(4)
                            ->get();

        // abi_r($products->count(), true);

$k=0;
// Nice! Lets move on and retrieve Documents
foreach ($products as $product) {
        # code...
        // Initialize
//        $product-> = 0.0;

    $product_date_from = $date_from->copy();
    $product_date_to   = $date_to->copy();

    foreach ($list_of_years as $year) {

        // abi_r($product->name);
        // abi_r($product_date_from);
        // abi_r($product_date_to);

        $product->{$year} = $class::
                          where('line_type', 'product')
                        ->where('product_id', $product->id)
                        ->whereHas('document', function ($query) use ( $customer_id, $product_date_from, $product_date_to, $document_reference_date, $is_invoiceable_flag) {

                                // Closed Documents only
                                $query->where($document_reference_date, '!=', null);

                                if ( $customer_id > 0 )
                                    $query->where('customer_id', $customer_id);

                                // Only invoiceable Documents when Documents are Customer Shipping Slips
                                if ( $is_invoiceable_flag )
                                    $query->where('is_invoiceable', '>', 0);

                                if ( $product_date_from )
                                    $query->where($document_reference_date, '>=', $product_date_from);
                                
                                if ( $product_date_to )
                                    $query->where($document_reference_date, '<=', $product_date_to);
                        })
                        ->sum($document_total_tax);

        $product_date_from->addYear();
        $product_date_to->addYear();

    }
    $k++;
    // if ($k==4) break;
}
// die();
// abi_r($products, true);
        // Lets get dirty!!
        // See: https://laraveldaily.com/laravel-excel-export-formatting-and-styling-cells/


        // Initialize the array which will be passed into the Excel generator.
        $data = [];

        $customer_label = (int) $customer_id > 0
                        ? Customer::findOrFail($customer_id)->name_regular
                        : 'todos';

        // Sheet Header Report Data
        $data[] = [Context::getContext()->company->name_fiscal];

        $row = [];
        $row[] = 'Listado de Ventas comparativas por Producto ('.l($model).') ';
        foreach ($list_of_years as $year) {
            // $row[] = '';
        }
        $row[] = '';
        $row[] = date('d M Y H:i:s');
        $data[] = $row;

        $data[] = ['Cliente: '. $customer_label];

        $ribbon = $document_total_tax == 'total_tax_incl' ?
                                            'Ventas son con Impuestos incluidos.' :
                                            'Ventas son sin Impuestos.';
        // $data[] = [ 'Listado de Ventas comparativas por Producto ('.l($model).') ', '', '', '', '', '', '', '', '', date('d M Y H:i:s')];
        $data[] = ['Meses: desde '.$selectorMonthList[$product_sales_month_from].' hasta '.$selectorMonthList[$product_sales_month_to].'. '.$ribbon];
        $data[] = [''];


        // Define the Excel spreadsheet headers
        $header_names = ['Referencia', 'Nombre', ];
        foreach ($list_of_years as $year) {
            $header_names[] = 'Año '.$year;
        }

        $data[] = $header_names;

        // Convert each member of the returned collection into an array,
        // and append it to the data array.

        // Initialize (colmn) totals
        $totals = [];
        foreach ($list_of_years as $year) {
            $totals[$year] = 0.0;
        }

        foreach ($products as $product) 
        {
                $row = [];
                $row[] = (string) $product->reference;
                $row[] = (string) $product->name;

                foreach ($list_of_years as $year) {
                    $row[] = (float) $product->{$year};

                    $totals[$year] += (float) $product->{$year};
                }
    
                $data[] = $row;

        }

        // Totals
        $data[] = [''];

        $row = [];
        $row[] = '';
        $row[] = 'Total:';
        foreach ($list_of_years as $year) {
            $row[] = (float) $totals[$year];
        }
        $data[] = $row;

        // abi_r($data, true);

//        $i = count($data);


        $styles = [];

        $w = count($data[5+1]);
        $styles[ 'A6:'.chr(ord('A') + $w - 1).'6' ] = ['font' => ['bold' => true]];

        $n = count($data);
        $m = $n;    //  - 3;
        $styles[ "B$m:".chr(ord('A') + $w - 1)."$n" ] = ['font' => ['bold' => true]];

        $columnFormats = [
            'A' => NumberFormat::FORMAT_TEXT,
//            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'C' => NumberFormat::FORMAT_NUMBER_00,
            'D' => NumberFormat::FORMAT_NUMBER_00,
        ];

        $mergeThis = 'C2:'.chr(ord('C') + $nbr_years).'2';
        // https://stackoverflow.com/questions/39314048/increment-letters-like-number-by-certain-value-in-php
        $merges = ['A1:B1', 'A2:B2', 'A3:B3', 'A4:B4', $mergeThis];

        $sheetTitle = 'Ventas ' . l($model);

        $export = new ArrayExport($data, $styles, $sheetTitle, $columnFormats, $merges);

        $sheetFileName = 'Ventas_Productos-' . l($model);

        // Generate and return the spreadsheet
        return Excel::download($export, $sheetFileName.'.xlsx');

    }

}