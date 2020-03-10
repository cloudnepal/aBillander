<?php 

namespace App;

class ProductionPlanner 
{
    public $production_sheet_id;
    public $due_date;
    public $orders_planned;
    public $products_planned;
	
    public function __construct( $production_sheet_id, $due_date )
    {
        //
        $this->production_sheet_id; 
        $this->due_date = $due_date;

        $this->orders_planned = collect([]);
        $this->products_planned = collect([]);
    }


    public function getPlannedOrders()
    {
        return $this->orders_planned;
    }


    public function addPlannedMultiLevel($data = [])
    {
        $fields = [ 'created_via', 'status',
                    'product_id', 'required_quantity', 'planned_quantity', 'due_date', 'schedule_sort_order',
                    'work_center_id', 'manufacturing_batch_size', 'machine_capacity', 'units_per_tray', 
                    'warehouse_id', 'production_sheet_id', 'notes'];

        $product = Product::where('procurement_type', 'manufacture')
                        ->orWhere('procurement_type', 'assembly')
                        ->findOrFail( $data['product_id'] );

        $bom     = $product->bom;

        $order_quantity = $data['planned_quantity'];

        $order = new ProductionOrder([
            'created_via' => $data['created_via'] ?? 'manual',
            'status'      => $data['status']      ?? 'planned',

            'product_id' => $product->id,
            'product_reference' => $product->reference,
            'product_name' => $product->name,
            'procurement_type' => $product->procurement_type,

            'required_quantity' => $order_quantity,
            'planned_quantity' => $order_quantity,
            'product_bom_id' => $bom ? $bom->id : 0,

            'due_date' => $data['due_date'],
//            'schedule_sort_order' => 0,
            'notes' => $data['notes'],

            'work_center_id' => $data['work_center_id'] ?? $product->work_center_id,

            'manufacturing_batch_size' => $product->manufacturing_batch_size,
            'machine_capacity' => $product->machine_capacity, 
            'units_per_tray' => $product->units_per_tray,
//            'warehouse_id' => '',
            'production_sheet_id' => $data['production_sheet_id'],
        ]);

        $this->orders_planned->push($order);





        // Order lines
        if ( !$bom ) return null;

        foreach( $bom->BOMmanufacturablelines as $line ) {

            $quantity = $order_quantity * ( $line->quantity / $bom->quantity ) * (1.0 + $line->scrap/100.0);

            $order = $this->addPlannedMultiLevel([
                'created_via' => $data['created_via'] ?? 'manual',
                'status'      => $data['status']      ?? 'planned',

                'product_id' => $line_product->id,
                'product_reference' => $line_product->reference,
                'product_name' => $line_product->name,
                'procurement_type' => $line_product->procurement_type,

                'required_quantity' => $quantity,
                'planned_quantity' => $quantity,
                'product_bom_id' => $line_product->bom ? $line_product->bom->id : 0,

                'due_date' => $data['due_date'],
//            'schedule_sort_order' => 0,
                'notes' => $data['notes'],

//                'work_center_id' => $data['work_center_id'] ?? $line_product->work_center_id,
                'manufacturing_batch_size' => $line_product->manufacturing_batch_size,  // $mbs,
    //            'warehouse_id' => '',
                'production_sheet_id' => $data['production_sheet_id'],
            ]);

            // if ($order)                $this->orders_planned->push($order);
        }

        // return $production_orders;
    }


    public function addExtraPlannedMultiLevel_old($data = [])
    {
        $fields = [ 'created_via', 'status',
                    'product_id', 'required_quantity', 'planned_quantity', 'due_date', 'schedule_sort_order',
                    'work_center_id', 'manufacturing_batch_size', 'machine_capacity', 'units_per_tray', 
                    'warehouse_id', 'production_sheet_id', 'notes'];

        // $product = Product::findOrFail( $data['product_id'] );
        $product = $this->products_planned->firstWhere('id', $data['product_id']) 
                 ?: Product::where($product->procurement_type == 'manufacture')
                            ->orWhere($product->procurement_type == 'assembly')
                            ->findOrFail( $data['product_id'] );
        
        $bom     = $product->bom;

        // Retrieve Planned Order for this Product
        // If no order, issue one
        if ( !$this->getPlannedOrders()->contains('product_id', $product->id) )
        $order = new ProductionOrder([
            'created_via' => $data['created_via'] ?? 'manual',
            'status'      => $data['status']      ?? 'planned',

            'product_id' => $product->id,
            'product_reference' => $product->reference,
            'product_name' => $product->name,
            'procurement_type' => $product->procurement_type,

            'required_quantity' => 0.0,
            'planned_quantity' => 0.0,
            'product_bom_id' => $bom ? $bom->id : 0,

            'due_date' => $data['due_date'],
//            'schedule_sort_order' => 0,
            'notes' => $data['notes'],

            'work_center_id' => $data['work_center_id'] ?? $product->work_center_id,

            'manufacturing_batch_size' => $product->manufacturing_batch_size,
            'machine_capacity' => $product->machine_capacity, 
            'units_per_tray' => $product->units_per_tray,
//            'warehouse_id' => '',
            'production_sheet_id' => $data['production_sheet_id'],
        ]);

        $this->orders_planned->push($order);

        // Do Continue

        $product_id = $product->id;
        $order_planned  = $data['planned_quantity'];
        $order_required = $data['required_quantity'];

        $this->orders_planned->transform(function ($item, $key) use ($product_id, $order_required, $order_planned) {
                        if($item->product_id == $product_id) {

                            $item->required_quantity += $order_required;
                            $item->planned_quantity  += $order_planned;         // Do not check bat size, as it should be checked before call to this function
                        } 

                        return $item;
                    }); 





        // Order lines
        if ( !$bom ) return null;

        foreach( $bom->BOMlines as $line ) {
            
             $line_product = $line->product;

             if ( !( 
                       ($line_product->procurement_type == 'manufacture') 
                    || ($line_product->procurement_type == 'assembly') 
                ) ) continue;

            
            // $quantity es la cantidad extra que hay que fabricar del hijo.
            // Para el padre es "planned", pero para el hijo es "required"
            $quantity = $order_planned * ( $line->quantity / $bom->quantity ) * (1.0 + $line->scrap/100.0);

            // Retrieve  Planned Order for this Product
            // If no order, issue one (toDo)
            $order = $this->getPlannedOrders()->where('product_id', $line_product->id)->first();

            // Let's see if we have something to manufacture.  Two use cases:
            $diff = $order->planned_quantity - $order->required_quantity;
            // [1] 
            if ( $diff >= $quantity )   // No more manufacturing needed!
            {
                $product_id = $order->product_id;
                $order_required = $quantity;
                $order_planned  = 0.0;
                
                $this->orders_planned->transform(function ($item, $key) use ($product_id, $order_required, $order_planned) {
                                if($item->product_id == $product_id) {

                                    $item->required_quantity += $order_required;
                                    $item->planned_quantity  += $order_planned;         // Do not check bat size, as it should be checked before call to this function
                                } 

                                return $item;
                            }); 
            }
            // [2] $diff < $quantity 
            else
            {
                $product_id = $order->product_id;
                $order_required = $quantity;
                // Total Planned Quantity will be determined by Total Required Quantity and Batch size
                $total_required = $order->required_quantity + $order_required;

                $nbt = ceil($total_required / $order->manufacturing_batch_size);
                $extra_quantity = $nbt * $order->manufacturing_batch_size - $total_required;

                $order_planned  = $extra_quantity - $order->planned_quantity;       // Maybe positive or negative amout. Sexy!
                
                $this->orders_planned->transform(function ($item, $key) use ($product_id, $order_required, $order_planned) {
                                if($item->product_id == $product_id) {

                                    $item->required_quantity += $order_required;
                                    $item->planned_quantity  += $order_planned;         // Do not check bat size, as it should be checked before call to this function
                                } 

                                return $item;
                            });


                $order = $this->addPlannedMultiLevel([
                    'created_via' => $data['created_via'] ?? 'manual',
                    'status'      => $data['status']      ?? 'planned',

                    'product_id' => $line_product->id,
                    'product_reference' => $line_product->reference,
                    'product_name' => $line_product->name,
                    'procurement_type' => $line_product->procurement_type,

                    'required_quantity' => $quantity,
                    'planned_quantity' => $quantity,
                    'product_bom_id' => $line_product->bom ? $line_product->bom->id : 0,

                    'due_date' => $data['due_date'],
    //            'schedule_sort_order' => 0,
                    'notes' => $data['notes'],

    //                'work_center_id' => $data['work_center_id'] ?? $line_product->work_center_id,
                    'manufacturing_batch_size' => $line_product->manufacturing_batch_size,  // $mbs,
        //            'warehouse_id' => '',
                    'production_sheet_id' => $data['production_sheet_id'],
                ]);
            }

        }

        // return $production_orders;
    }




    public function addExtraPlannedMultiLevel($product_id, $new_required = 0.0)
    {
        $product = $this->products_planned->firstWhere('id', $product_id) 
                 ?: Product::where('procurement_type', 'manufacture')
                            ->orWhere('procurement_type', 'assembly')
                            ->findOrFail( $product_id );
        
        $bom     = $product->bom;

        // Retrieve Planned Order for this Product
        // If no order, issue one
        if ( !$this->getPlannedOrders()->contains('product_id', $product->id) )
        {
            $order = new ProductionOrder([
                'created_via' => 'manufacturing',
                'status'      => 'planned',

                'product_id' => $product->id,
                'product_reference' => $product->reference,
                'product_name' => $product->name,
                'procurement_type' => $product->procurement_type,

                'required_quantity' => 0.0,
                'planned_quantity' => 0.0,
                'product_bom_id' => $bom ? $bom->id : 0,

                'due_date' => $this->due_date,
    //            'schedule_sort_order' => 0,
    //            'notes' => $data['notes'],

                'work_center_id' => $product->work_center_id,

                'manufacturing_batch_size' => $product->manufacturing_batch_size,
                'machine_capacity' => $product->machine_capacity, 
                'units_per_tray' => $product->units_per_tray,
    //            'warehouse_id' => '',
                'production_sheet_id' => $this->production_sheet_id,
            ]);

            $this->orders_planned->push($order);
        }


        // Do Continue
        $quantity = $new_required ;

        // Let's see if we have something to manufacture.  Two use cases:
        $diff = $order->planned_quantity - $order->required_quantity;
        // [1] 
        if ( $diff >= $quantity )   // No more manufacturing needed!
        {
            $product_id = $order->product_id;
            $order_required = $quantity;
            $order_planned  = 0.0;
            
            $this->orders_planned->transform(function ($item, $key) use ($product_id, $order_required, $order_planned) {
                            if($item->product_id == $product_id) {

                                $item->required_quantity += $order_required;
                                $item->planned_quantity  += $order_planned;         // Do not check bat size, as it should be checked before call to this function
                            } 

                            return $item;
                        });

            // That's all Folcks!
            return ;
        }

        // [2] $diff < $quantity 
        $product_id = $order->product_id;
        $order_required = $quantity;
        // Total Planned Quantity will be determined by Total Required Quantity and Batch size
        $total_required = $order->required_quantity + $order_required;

        $nbt = ceil($total_required / $order->manufacturing_batch_size);
        $extra_quantity = $nbt * $order->manufacturing_batch_size - $total_required;

        $order_planned  = $total_required + $extra_quantity - $order->planned_quantity;       // Maybe positive or negative amout. Sexy!

        
        $this->orders_planned->transform(function ($item, $key) use ($product_id, $order_required, $order_planned) {
                        if($item->product_id == $product_id) {

                            $item->required_quantity += $order_required;
                            $item->planned_quantity  += $order_planned;         // Do not check bat size, as it should be checked before call to this function
                        } 

                        return $item;
                    });


        // In the end, 
        $extra_manufacture = $extra_quantity;


        // Order lines
        if ( !$bom ) return null;

        foreach( $bom->BOMmanufacturablelines as $line ) {
            
            // $quantity es la cantidad extra que hay que fabricar del hijo.
            // Para el padre es "planned", pero para el hijo es "required"
            $quantity = $extra_manufacture * ( $line->quantity / $bom->quantity ) * (1.0 + $line->scrap/100.0);
                
            $order = $this->addExtraPlannedMultiLevel($line_product->id, $quantity);

        }

    }


    public function groupPlannedOrders( $withStock = false )
    {
        $this->orders_planned = $this->getPlannedOrders()
                ->groupBy('product_id')->reduce(function ($result, $group) {
                      $reduced = $group->first();

                      $reduced->required_quantity = $group->sum('required_quantity');
                      $reduced->planned_quantity  = $group->sum('planned_quantity');

                      return $result->put($reduced->product_id, $reduced);

                }, collect());

        // Load Products in memory
        $pIDs = $this->orders_planned->pluck('product_id');
        $this->products_planned = Product::whereIn('id', $pIDs)->get();

        $products_planned = &$this->products_planned;

        // Stock adjustment
        $this->orders_planned->transform(function($order, $key) use ($products_planned, $withStock) {
                      $product = $products_planned->firstWhere('id', $order->product_id);
                      $product_stock = 0.0;

                      if ($product->procurement_type == 'assembly')
                      // Only Assembies since Manufacture Products are already adjusted in STEP 1
                      if ( $withStock )
                      {
                            if ( $product->stock_control )
                                $product_stock = $product->quantity_onhand;
                      }

            $order->required_quantity = $order->required_quantity - $product_stock;
            $order->planned_quantity  = $order->planned_quantity  - $product_stock;
            return $order;
        });


        abi_r('* *************************** *');
        abi_r($this->getPlannedOrders());

        // die();
    }
	
}