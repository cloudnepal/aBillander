<?php 

namespace App\Traits;

use Illuminate\Http\Request;

use App\Configuration;
use App\Context;
use App\Product;
use App\ShippingMethod;

use App\Lot;
use App\LotItem;

trait ProductionOrderLotFormsControllerTrait
{


    public function setDocumentLotsLines($document_id)
    {

        $document = $this->productionorder
                        ->with('productionorderlines')
                        ->with('productionorderlines.product')
                        ->with('productionorderlines.lotitems')
                        ->find((int) $document_id);

        if ( !$document )
            return response()->json( [] );

        $lines = [];


foreach ($document->productionorderlines as $document_line) {
        // code...
        if( !( $product = $document_line->product ) )
            continue;

        if( ! $product->lot_tracking )
            continue;

        // Let's get available lots for this line product
        $lots = $product->availableLotsSorted();
        // $lots are well ordered according to product lot policy

        // abi_r($document_line->lotitems, true);

        // Lets see if there are lot allocated quantities
        if ( $document_line->lotitems->count() != 0 )
            // Delete current allocations
            $document_line->lotitems()->each(function($lotitem) {
                $lotitem->delete(); // <-- direct deletion
             });

        $quantity = $document_line->required_quantity;
        // Pre-assign lots & build $lots_allocated collection
        foreach ($lots as $lot) {
            if ($quantity <= 0) break;
            # code...
            $lot_available_qty = $lot->availableQuantity();
            $allocable = $quantity > $lot_available_qty ?
                                $lot_available_qty :
                                $quantity      ;

            if ( $allocable == 0 )
                continue;

            $data = [
                'lot_id' => $lot->id,
                'is_reservation' => 1,
                'quantity' => $allocable,
            ];

            $lot_item = LotItem::create( $data );
            $document_line->lotitems()->save($lot_item);

            $quantity = $quantity - $allocable;
        }

        if( $quantity > 0 )
        {
            // Not enough available quantities from lots!!!
        }

        $document_line->load('lotitems');
        $document_line->update(['real_quantity' => $document_line->lotitems->sum('quantity')]);

        $lines[] = $document_line;

}
        
        

        return response()->json( [
            'success' => 'OK',
            'lines' => $lines,
 //           'lots_allocated' => $document_line->lotitems,
        ] );

        



        $document_line = $this->productionorder_line
                        ->with('product')
                        ->with('measureunit')
                        ->with('lotitems')
                        ->find($line_id);

        if ( !$document_line )
            return response()->json( [] );
/*
        $unit_customer_final_price = Configuration::get('PRICES_ENTERED_WITH_TAX') ? 
                                        $order_line->unit_customer_final_price * ( 1.0 + $order_line->tax_percent / 100.0 ) : 
                                        $order_line->unit_customer_final_price ;
*/
        $product = $document_line->product;
        $tax = $document_line->tax;

        $currency = Context::getContext()->currency;

        $ecotax_amount = $product && $product->ecotax ? 
                              $product->as_priceable($product->ecotax->amount) :
                              '0.00';

        $ecotax_value_label = $ecotax_amount.' '.$currency->name;

        $pmu_conversion_rate = 1.0;
        $package_label = '';

        if ( $document_line->package_measure_unit_id != $document_line->measure_unit_id)
        {
            $pmu_conversion_rate = $document_line->pmu_conversion_rate;

            $package_label = (int) $pmu_conversion_rate.'x'.$document_line->measureunit->name;
        }

        // abi_r($document_line->toArray());die();

        // Let's get available lots for this line product
        $lots = $document_line->product->availableLotsSorted();
        // $lots are well ordered according to product lot policy

        // Lets see if there are lot allocated quantities
        $lots_allocated = $document_line->lotitems;
        if ( $lots_allocated->count() == 0 )
        {
            $quantity = $document_line->quantity;
            // Pre-assign lots & build $lots_allocated collection
            foreach ($lots as $lot) {
                if ($quantity <= 0) break;
                # code...
                $lot_available_qty = $lot->availableQuantity();
                $allocable = $quantity > $lot_available_qty ?
                                    $lot_available_qty :
                                    $quantity      ;

                if ( $allocable == 0 )
                    continue;

                $data = [
                    'lot_id' => $lot->id,
                    'is_reservation' => 1,
                    'quantity' => $allocable,
                ];

                $lot_item = LotItem::create( $data );
                $document_line->lotitems()->save($lot_item);
                $lots_allocated->push($lot_item);

                $quantity = $quantity - $allocable;
            }

            if( $quantity > 0 )
            {
                // Not enough available quantities from lots!!!
            }
        }
        
        // Calculate allocated quantity for this line product
        foreach ($lots as $lot) {
            # code...
            // $lot->allocated_to_line = $lot->allocatedByCustomerShippingSlipLineId( $document_line->id ); 
            // Another way
            // Is $lot allocated?
            $alloc = $lots_allocated->where('lot_id', $lot->id)->first();
            $lot->allocated_to_line = $alloc ? $alloc->quantity : 0;

            // Lot is not allocable if $lot->availableQuantity() == 0 and $lot->allocated_to_line == 0
            $lot->not_allocable = ($lot->availableQuantity() <= 0.0) && ($lot->allocated_to_line == 0.0);
        }

        // die();

        $nopagination = 1;

        $lots_view = view('products._chunck_lots')->with(compact('lots', 'nopagination'))->render();




        return response()->json( $document_line->toArray() + [
//            'unit_customer_final_price' => $unit_customer_final_price,
            'tax_label' => "%)",
            'ecotax_value_label' => $ecotax_value_label,

            'pmu_conversion_rate' => $pmu_conversion_rate,
            'package_label' => $package_label,

            'lots_view' => $lots_view,
        ] );
    }




    

    public function FormForProductLots( $action )
    {;

    

        switch ( $action ) {
            case 'edit':
                # code...
                return view('production_orders._form_for_product_lots_edit');
                break;
            
            case 'create':
                # code...
                // return view('production_orders._form_for_product_lots_create');
                break;
            
            default:
                # code...
                // Form for action not supported
                return response()->json( [
                            'msg' => 'ERROR',
                            'data' => $action
                    ] );
                break;
        }
        
    }



    public function getDocumentLotsLine($document_id, $line_id)
    {
        $document_line = $this->productionorder_line
                        ->with('product')
                        ->with('measureunit')
                        ->with('lotitems')
                        ->find($line_id);

        if ( !$document_line )
            return response()->json( [] );
/*
        $unit_customer_final_price = Configuration::get('PRICES_ENTERED_WITH_TAX') ? 
                                        $order_line->unit_customer_final_price * ( 1.0 + $order_line->tax_percent / 100.0 ) : 
                                        $order_line->unit_customer_final_price ;
*/
        $product = $document_line->product;
        $tax = $document_line->tax;

        $currency = Context::getContext()->currency;

        $ecotax_amount = $product && $product->ecotax ? 
                              $product->as_priceable($product->ecotax->amount) :
                              '0.00';

        $ecotax_value_label = $ecotax_amount.' '.$currency->name;

        $pmu_conversion_rate = 1.0;
        $package_label = '';

        if ( $document_line->package_measure_unit_id != $document_line->measure_unit_id)
        {
            $pmu_conversion_rate = $document_line->pmu_conversion_rate;

            $package_label = (int) $pmu_conversion_rate.'x'.$document_line->measureunit->name;
        }

        // abi_r($document_line->toArray());die();

        // Let's get available lots for this line product
        $lots = $document_line->product->availableLotsSorted();
        // $lots are well ordered according to product lot policy

        // Lets see if there are lot allocated quantities
        $lots_allocated = $document_line->lotitems;
        if ( $lots_allocated->count() == 0 )
        {
            $quantity = $document_line->quantity;
            // Pre-assign lots & build $lots_allocated collection
            foreach ($lots as $lot) {
                if ($quantity <= 0) break;
                # code...
                $lot_available_qty = $lot->availableQuantity();
                $allocable = $quantity > $lot_available_qty ?
                                    $lot_available_qty :
                                    $quantity      ;

                if ( $allocable == 0 )
                    continue;

                $data = [
                    'lot_id' => $lot->id,
                    'is_reservation' => 1,
                    'quantity' => $allocable,
                ];

                $lot_item = LotItem::create( $data );
                $document_line->lotitems()->save($lot_item);
                $lots_allocated->push($lot_item);

                $quantity = $quantity - $allocable;
            }

            if( $quantity > 0 )
            {
                // Not enough available quantities from lots!!!
            }
        }
        
        // Calculate allocated quantity for this line product
        foreach ($lots as $lot) {
            # code...
            // $lot->allocated_to_line = $lot->allocatedByCustomerShippingSlipLineId( $document_line->id ); 
            // Another way
            // Is $lot allocated?
            $alloc = $lots_allocated->where('lot_id', $lot->id)->first();
            $lot->allocated_to_line = $alloc ? $alloc->quantity : 0;

            // Lot is not allocable if $lot->availableQuantity() == 0 and $lot->allocated_to_line == 0
            $lot->not_allocable = ($lot->availableQuantity() <= 0.0) && ($lot->allocated_to_line == 0.0);
        }

        // die();

        $nopagination = 1;

        $lots_view = view('products._chunck_lots')->with(compact('lots', 'nopagination'))->render();




        return response()->json( $document_line->toArray() + [
//            'unit_customer_final_price' => $unit_customer_final_price,
            'tax_label' => "%)",
            'ecotax_value_label' => $ecotax_value_label,

            'pmu_conversion_rate' => $pmu_conversion_rate,
            'package_label' => $package_label,

            'lots_view' => $lots_view,
        ] );
    }




    public function updateDocumentLotsLine(Request $request, $line_id)
    {
        // if ( $request->has('supplier_id') )
        //    return $this->updateSupplierDocumentLine($request, $line_id);
        
        $line_type = $request->input('line_type', '');

        switch ( $line_type ) {
            case 'product':
                # code...
                return $this->updateDocumentLineProductLots($request, $line_id);
                break;
            
            case 'service':
            case 'shipping':
                # code...
                // return $this->updateDocumentLineService($request, $line_id);
                break;
            
            case 'comment':
                # code...
                // return $this->updateDocumentLineComment($request, $line_id);
                break;
            
            default:
                # code...
                // Document Line Type not supported
                return response()->json( [
                            'msg' => 'ERROR',
                            'data' => $request->all()
                    ] );
                break;
        }
    }

    public function updateDocumentLineProductLots(Request $request, $line_id)
    {

        $params = [
//            'prices_entered_with_tax' => $pricetaxPolicy,
//            'discount_percent' => $request->input('discount_percent', 0.0),
//            'unit_customer_final_price' => $request->input('unit_customer_final_price'),

//            'line_sort_order' => $request->input('line_sort_order'),
//            'notes' => $request->input('notes'),
        ];
/*
        // More stuff
        if ($request->has('quantity')) 
            $params['quantity'] = $request->input('quantity');

        if ($request->has('prices_entered_with_tax')) 
            $params['prices_entered_with_tax'] = $request->input('prices_entered_with_tax');

        if ($request->has('discount_percent')) 
            $params['discount_percent'] = $request->input('discount_percent');

        if ($request->has('unit_customer_final_price')) 
            $params['unit_customer_final_price'] = $request->input('unit_customer_final_price');

        if ($request->has('line_sort_order')) 
            $params['line_sort_order'] = $request->input('line_sort_order');

        if ($request->has('pmu_label')) 
            $params['pmu_label'] = $request->input('pmu_label');

        if ($request->has('extra_quantity_label')) 
            $params['extra_quantity_label'] = $request->input('extra_quantity_label');

        if ($request->has('notes')) 
            $params['notes'] = $request->input('notes');


        if ($request->has('name')) 
            $params['name'] = $request->input('name');

        if ($request->has('sales_equalization')) 
            $params['sales_equalization'] = $request->input('sales_equalization');

        if ($request->has('measure_unit_id')) 
            $params['measure_unit_id'] = $request->input('measure_unit_id');

        if ($request->has('sales_rep_id')) 
            $params['sales_rep_id'] = $request->input('sales_rep_id');

        if ($request->has('commission_percent')) 
            $params['commission_percent'] = $request->input('commission_percent');
*/

        if ($request->has('lot_references')) 
            $params['lot_references'] = $request->input('lot_references');

        // Let's Rock!
        $document_line = $this->document_line
                        ->with( 'document' )
                        ->find($line_id);

        if ( !$document_line )
            return response()->json( [
                    'msg' => 'ERROR',
                    'data' => $line_id,
            ] );

        
        $document = $document_line->document;
//        $document = $this->document->where('id', $this->model_snake_case.'_id')->first();

        $document_line->update( $params );


        return response()->json( [
                'msg' => 'OK',
                'data' => $document_line->toArray()
        ] );
    }

}