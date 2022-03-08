<?php

namespace App\Http\Controllers;

use App\Events\CustomerQuotationConfirmed;
use App\Helpers\Price;
use App\Models\Combination;
use App\Models\Configuration;
use App\Models\Context;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceLine;
use App\Models\CustomerInvoiceLineTax;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderLine;
use App\Models\CustomerOrderLineTax;
use App\Models\CustomerQuotation as Document;
use App\Models\CustomerQuotationLine as DocumentLine;
use App\Models\CustomerShippingSlip;
use App\Models\CustomerShippingSlipLine;
use App\Models\CustomerShippingSlipLineTax;
use App\Models\DocumentAscription;
use App\Models\Product;
use App\Models\SalesRep;
use App\Models\Sequence;
use App\Models\ShippingMethod;
use App\Models\Tax;
use App\Models\Template;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CustomerQuotationsController extends BillableController
{

   public function __construct(Customer $customer, Document $document, DocumentLine $document_line)
   {
        parent::__construct();

        $this->model_class = Document::class;

        $this->customer = $customer;
        $this->document = $document;
        $this->document_line = $document_line;
   }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $model_path = $this->model_path;
        $view_path = $this->view_path;

        $documents = $this->document
                            ->with('customer')
//                            ->with('currency')
//                            ->with('paymentmethod')
                            ->orderBy('document_date', 'desc')
                            ->orderBy('id', 'desc');        // ->get();

        $documents = $documents->paginate( Configuration::get('DEF_ITEMS_PERPAGE') );

        $documents->setPath($this->model_path);

        return view($this->view_path.'.index', $this->modelVars() + compact('documents'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function indexByCustomer($id, Request $request)
    {
        $model_path = $this->model_path;
        $view_path = $this->view_path;

        $items_per_page = intval($request->input('items_per_page', Configuration::getInt('DEF_ITEMS_PERPAGE')));
        if ( !($items_per_page >= 0) ) 
            $items_per_page = Configuration::getInt('DEF_ITEMS_PERPAGE');

        $sequenceList = Sequence::listFor( 'App\\CustomerInvoice' );

        $templateList = Template::listFor( 'App\\CustomerInvoice' );

        $customer = $this->customer->findOrFail($id);

        $documents = $this->document
                            ->where('customer_id', $id)
//                            ->with('customer')
                            ->with('currency')
//                            ->with('paymentmethod')
                            ->orderBy('document_date', 'desc')
                            ->orderBy('id', 'desc');        // ->get();

        $documents = $documents->paginate( $items_per_page );

        // abi_r($this->model_path, true);

        $documents->setPath($id);

        return view($this->view_path.'.index_by_customer', $this->modelVars() + compact('customer', 'documents', 'sequenceList', 'templateList', 'items_per_page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model_path = $this->model_path;
        $view_path = $this->view_path;
//        $model_snake_case = $this->model_snake_case;

        // Some checks to start with:

        $sequenceList = $this->document->sequenceList();

        $templateList = $this->document->templateList();

        if ( !(count($sequenceList)>0) )
            return redirect($this->model_path)
                ->with('error', l('There is not any Sequence for this type of Document &#58&#58 You must create one first', [], 'layouts'));
        
        return view($this->view_path.'.create', $this->modelVars() + compact('sequenceList', 'templateList'));
    
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createWithCustomer($customer_id)
    {
        $model_path = $this->model_path;
        $view_path = $this->view_path;

        // Some checks to start with:

        $sequenceList = $this->document->sequenceList();

        if ( !count($sequenceList) )
            return redirect($this->model_path)
                ->with('error', l('There is not any Sequence for this type of Document &#58&#58 You must create one first', [], 'layouts'));


        // Do the Mambo!!!
        try {
            $customer = Customer::with('addresses')->findOrFail( $customer_id );

        } catch(ModelNotFoundException $e) {
            // No Customer available, ask for one
            return redirect()->back()
                    ->with('error', l('The record with id=:id does not exist', ['id' => $customer_id], 'layouts'));
        }
        
        return view($this->view_path.'.create', $this->modelVars() + compact('sequenceList', 'customer_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Dates (cuen)
        $this->mergeFormDates( ['document_date', 'delivery_date', 'valid_until_date'], $request );

        $rules = $this->document::$rules;

        $rules['shipping_address_id'] = str_replace('{customer_id}', $request->input('customer_id'), $rules['shipping_address_id']);
        $rules['invoicing_address_id'] = $rules['shipping_address_id'];

        $this->validate($request, $rules);

        $customer = Customer::with('addresses')->findOrFail(  $request->input('customer_id') );

        // Extra data
//        $seq = Sequence::findOrFail( $request->input('sequence_id') );
//        $doc_id = $seq->getNextDocumentId();

        $extradata = [  'user_id'              => Context::getContext()->user->id,

                        'sequence_id'          => $request->input('sequence_id') ?? Configuration::getInt('DEF_'.strtoupper( $this->getParentModelSnakeCase() ).'_SEQUENCE'),

                        'document_discount_percent' => $customer->discount_percent,
                        'document_ppd_percent'      => $customer->discount_ppd_percent,

                        'created_via'          => 'manual',
                        'status'               =>  'draft',
                        'locked'               => 0,
                     ];

        $request->merge( $extradata );

        $document = $this->document->create($request->all());

        // Move on
        if ($request->has('nextAction'))
        {
            switch ( $request->input('nextAction') ) {
                case 'saveAndConfirm':
                    # code...
                    $document->confirm();

                    break;
                
                default:
                    # code...
                    break;
            }
        }

        // Maybe...
//        if (  Configuration::isFalse('CUSTOMER_ORDERS_NEED_VALIDATION') )
//            $customerOrder->confirm();

        return redirect($this->model_path.'/'.$document->id.'/edit')
                ->with('success', l('This record has been successfully created &#58&#58 (:id) ', ['id' => $document->id], 'layouts'));

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomerQuotation  $customerorder
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect($this->model_path.'/'.$id.'/edit');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CustomerQuotation  $customerorder
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        // Little bit Gorrino style...
        // Find by document_reference (if supplied one)
        if ( $request->has('document_reference') )
        {
            $document = $this->document->where('document_reference', $request->input('document_reference'))->firstOrFail();

            // $request->request->remove('document_reference');
            // $this->edit($document->id, $request);

            return redirect($this->model_path.'/'.$document->id.'/edit');
        }
        else
        {
            $document = $this->document->findOrFail($id);
        }

        $sequenceList = $this->document->sequenceList();

        $templateList = $this->document->templateList();

        $customer = Customer::find( $document->customer_id );

        $addressBook       = $customer->addresses;

        $theId = $customer->invoicing_address_id;
        $invoicing_address = $addressBook->filter(function($item) use ($theId) {    // Filter returns a collection!
            return $item->id == $theId;
        })->first();

        $addressbookList = array();
        foreach ($addressBook as $address) {
            $addressbookList[$address->id] = $address->alias;
        }

        // Dates (cuen)
        $this->addFormDates( ['document_date', 'delivery_date', 'export_date', 'valid_until_date'], $document );

        return view($this->view_path.'.edit', $this->modelVars() + compact('customer', 'invoicing_address', 'addressBook', 'addressbookList', 'document', 'sequenceList', 'templateList'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CustomerQuotation  $customerorder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Document $customerquotation)
    {
        // Dates (cuen)
        $this->mergeFormDates( ['document_date', 'delivery_date', 'valid_until_date'], $request );

        $rules = $this->document::$rules;

        $rules['shipping_address_id'] = str_replace('{customer_id}', $request->input('customer_id'), $rules['shipping_address_id']);
        $rules['invoicing_address_id'] = $rules['shipping_address_id'];

        $this->validate($request, $rules);
/*
        // Extra data
        $seq = Sequence::findOrFail( $request->input('sequence_id') );
        $doc_id = $seq->getNextDocumentId();

        $extradata = [  'document_prefix'      => $seq->prefix,
                        'document_id'          => $doc_id,
                        'document_reference'   => $seq->getDocumentReference($doc_id),

                        'user_id'              => Context::getContext()->user->id,

                        'created_via'          => 'manual',
                        'status'               =>  Configuration::get('CUSTOMER_ORDERS_NEED_VALIDATION') ? 'draft' : 'confirmed',
                        'locked'               => 0,
                     ];

        $request->merge( $extradata );
*/
        $document = $customerquotation;

        $need_update_totals = (
            $request->input('document_ppd_percent', $document->document_ppd_percent) != $document->document_ppd_percent 
        ) ? true : false;

        $document->fill($request->all());

        // Reset Export date
        // if ( $request->input('export_date_form') == '' ) $document->export_date = null;

        $document->save();

        if ( $need_update_totals ) $document->makeTotals();

        // Move on
        if ($request->has('nextAction'))
        {
            switch ( $request->input('nextAction') ) {
                case 'saveAndConfirm':
                    # code...
                    $document->confirm();

                    break;
                
                case 'saveAndContinue':
                    # code...

                    break;
                
                default:
                    # code...
                    break;
            }
        }

        $nextAction = $request->input('nextAction', '');
        
        if ( $nextAction == 'saveAndContinue' ) 
            return redirect($this->model_path.'/'.$document->id.'/edit')
                ->with('success', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $document->id], 'layouts'));

        return redirect($this->model_path)
                ->with('success', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $document->id], 'layouts'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerQuotation  $customerorder
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $document = $this->document->findOrFail($id);

        if( !$document->deletable )
            return redirect()->back()
                ->with('error', l('This record cannot be deleted because its Status &#58&#58 (:id) ', ['id' => $id], 'layouts'));

        $document->delete();

        return redirect($this->model_path)      // redirect()->back()
                ->with('success', l('This record has been successfully deleted &#58&#58 (:id) ', ['id' => $id], 'layouts'));
    }


    /**
     * Manage Status.
     *
     * ******************************************************************************************************************************* *
     * 
     */

    protected function confirm(Document $document)
    {
        // Can I?
        if ( $document->lines->count() == 0 )
        {
            return redirect()->back()
                ->with('error', l('Unable to update this record &#58&#58 (:id) ', ['id' => $document->id], 'layouts').' :: '.l('Document has no Lines', 'layouts'));
        }

        if ( $document->onhold )
        {
            return redirect()->back()
                ->with('error', l('Unable to update this record &#58&#58 (:id) ', ['id' => $document->id], 'layouts').' :: '.l('Document is on-hold', 'layouts'));
        }

        // Confirm
        if ( $document->confirm() )
            return redirect()->back()       // ->route($this->model_path.'.index')
                    ->with('success', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $document->id], 'layouts').' ['.$document->document_reference.']');
        

        return redirect()->back()
                ->with('error', l('Unable to update this record &#58&#58 (:id) ', ['id' => $document->id], 'layouts'));
    }

    protected function unConfirm(Document $document)
    {
        // Can I?
        if ( $document->status != 'confirmed' )
        {
            return redirect()->back()
                ->with('error', l('Unable to update this record &#58&#58 (:id) ', ['id' => $document->id], 'layouts').' :: '.l('Document has no Lines', 'layouts'));
        }

        // UnConfirm
        if ( $document->unConfirmDocument() )
            return redirect()->back()
                    ->with('success', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $document->id], 'layouts').' ['.$document->document_reference.']');
        

        return redirect()->back()
                ->with('error', l('Unable to update this record &#58&#58 (:id) ', ['id' => $document->id], 'layouts'));
    }


    protected function onholdToggle(Document $document)
    {
        // No checks. A closed document can be set to "onhold". Maybe usefull...

        // Toggle
        $toggle = $document->onhold > 0 ? 0 : 1;
        $document->onhold = $toggle;
        
        $document->save();

        return redirect()->back()
                ->with('success', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $document->id], 'layouts').' ['.$document->document_reference.']');
    }


    protected function close(Document $document)
    {
        // Can I?
        if ( $document->lines->count() == 0 )
        {
            return redirect()->back()
                ->with('error', l('Unable to update this record &#58&#58 (:id) ', ['id' => $document->id], 'layouts').' :: '.l('Document has no Lines', 'layouts'));
        }

        if ( $document->onhold )
        {
            return redirect()->back()
                ->with('error', l('Unable to update this record &#58&#58 (:id) ', ['id' => $document->id], 'layouts').' :: '.l('Document is on-hold', 'layouts'));
        }

        // Close
        if ( $document->close() )
            return redirect()->back()       // ->route($this->model_path.'.index')
                    ->with('success', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $document->id], 'layouts').' ['.$document->document_reference.']');
        

        return redirect()->back()
                ->with('error', l('Unable to update this record &#58&#58 (:id) ', ['id' => $document->id], 'layouts'));
    }


    protected function unclose(Document $document)
    {

        if ( $document->status != 'closed' )
        {
            return redirect()->back()
                ->with('error', l('Unable to update this record &#58&#58 (:id) ', ['id' => $document->id], 'layouts').' :: '.l('Document is not closed', 'layouts'));
        }

        // Unclose (back to "confirmed" status)
        if ( $document->unclose() )
            return redirect()->back()
                    ->with('success', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $document->id], 'layouts').' ['.$document->document_reference.']');


        return redirect()->back()
                ->with('error', l('Unable to update this record &#58&#58 (:id) ', ['id' => $document->id], 'layouts'));
    }




    protected function getTodaysQuotations()
    {
        $model_path = $this->model_path;
        $view_path = $this->view_path;

        $documents = $this->document
                            ->where('delivery_date', \Carbon\Carbon::now())
                            ->orWhere('delivery_date', null)
                            ->with('customer')
//                            ->with('currency')
//                            ->with('paymentmethod')
                            ->orderBy('delivery_date', 'desc')
                            ->orderBy('id', 'desc');        // ->get();

        $documents = $documents->paginate( Configuration::get('DEF_ITEMS_PERPAGE') );

        $documents->setPath('today');

        return view($this->view_path.'.index_for_today', $this->modelVars() + compact('documents'));
    }


    protected function getInvoiceableQuotations($id, Request $request)
    {
        $model_path = $this->model_path;
        $view_path = $this->view_path;

        $items_per_page = intval($request->input('items_per_page', Configuration::getInt('DEF_ITEMS_PERPAGE')));
        if ( !($items_per_page >= 0) ) 
            $items_per_page = Configuration::getInt('DEF_ITEMS_PERPAGE');

        $sequenceList = Sequence::listFor( 'App\\CustomerInvoice' );

        $templateList = Template::listFor( 'App\\CustomerInvoice' );

        $customer = $this->customer->findOrFail($id);

        $documents = $this->document
                            ->where('customer_id', $id)
                            ->where('status', 'closed')
                            ->where('invoiced_at', null)
//                            ->with('customer')
                            ->with('currency')
//                            ->with('paymentmethod')
                            ->orderBy('document_date', 'desc')
                            ->orderBy('id', 'desc');        // ->get();

        $documents = $documents->paginate( $items_per_page );

        // abi_r($this->model_path, true);

        $documents->setPath($id);

        return view($this->view_path.'.index_by_customer', $this->modelVars() + compact('customer', 'documents', 'sequenceList', 'templateList', 'items_per_page'));
    }


    public function createGroupInvoice( Request $request )
    {
        // ProductionSheetsController
        $document_group = $request->input('document_group', []);

        if ( count( $document_group ) == 0 ) 
            return redirect()->route('customer.invoiceable.orders', $request->input('customer_id'))
                ->with('warning', l('No records selected. ', 'layouts').l('No action is taken &#58&#58 (:id) ', ['id' => ''], 'layouts'));
        
        // Dates (cuen)
        $this->mergeFormDates( ['document_date'], $request );

        $rules = $this->document::$rules_createinvoice;

        $this->validate($request, $rules);

        // Set params for group
        $params = $request->only('customer_id', 'template_id', 'sequence_id', 'document_date');

        // abi_r($params, true);

        return $this->invoiceDocumentList( $document_group, $params );
    } 

    public function createInvoice($id)
    {
        $document = $this->document
                            ->with('customer')
                            ->findOrFail($id);
        
        $customer = $document->customer;

        $params = [
            'customer_id'   => $customer->id, 
            'template_id'   => $customer->getInvoiceTemplateId(), 
            'sequence_id'   => $customer->getInvoiceSequenceId(), 
            'document_date' => \Carbon\Carbon::now()->toDateString(),
        ];

        // abi_r($params, true);
        
        return $this->invoiceDocumentList( [$id], $params );
    }



    
    public function invoiceDocumentList( $list, $params )
    {

//        1.- Recuperar los documntos
//        2.- Comprobar que están todos los de la lista ( comparando count() )

        try {

            $customer = $this->customer
                                ->with('currency')
                                ->findOrFail($params['customer_id']);

            $documents = $this->document
                                ->where('status', 'closed')
                                ->where('invoiced_at', null)
                                ->with('lines')
                                ->with('lines.linetaxes')
    //                            ->with('customer')
    //                            ->with('currency')
    //                            ->with('paymentmethod')
                                ->orderBy('document_date', 'asc')
                                ->orderBy('id', 'asc')
                                ->findOrFail( $list );
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return redirect()->back()
                    ->with('error', l('Some records in the list [ :id ] do not exist', ['id' => implode(', ', $list)], 'layouts'));
            
        }

//        4.- Cear cabecera

        // Header
        // Common data
        $data = [
//            'company_id' => $this->company_id,
            'customer_id' => $customer->id,
//            'user_id' => $this->,

            'sequence_id' => $params['sequence_id'],

            'created_via' => 'aggregate_shipping_slips',

            'document_date' => $params['document_date'],

            'currency_conversion_rate' => $customer->currency->conversion_rate,
//            'down_payment' => $this->down_payment,

            'total_currency_tax_incl' => $documents->sum('total_currency_tax_incl'),
            'total_currency_tax_excl' => $documents->sum('total_currency_tax_excl'),
//            'total_currency_paid' => $this->total_currency_paid,

            'total_tax_incl' => $documents->sum('total_tax_incl'),
            'total_tax_excl' => $documents->sum('total_tax_excl'),

//            'commission_amount' => $this->commission_amount,

            // Skip notes

            'status' => 'draft',
            'locked' => 0,

            'invoicing_address_id' => $customer->invoicing_address_id,
//            'shipping_address_id' => $this->shipping_address_id,
//            'warehouse_id' => $this->warehouse_id,
//            'shipping_method_id' => $this->shipping_method_id ?? $this->customer->shipping_method_id ?? Configuration::getInt('DEF_CUSTOMER_SHIPPING_METHOD'),
//            'carrier_id' => $this->carrier_id,
            'sales_rep_id' => $customer->sales_rep_id,
            'currency_id' => $customer->currency->id,
            'payment_method_id' => $customer->getPaymentMethodId(),
            'template_id' => $params['template_id'],
        ];

        // Model specific data
        $extradata = [
            'type' => 'invoice',
//            'payment_status' => 'pending',
//            'stock_status' => 'completed',
        ];


        // Let's get dirty
//        CustomerInvoice::unguard();
        $invoice = CustomerInvoice::create( $data + $extradata );
//        CustomerInvoice::reguard();


//        5a.- Añadir Albarán
//        5b.- Crear enlaces para trazabilidad de documentos
        // Initialize grouped lines collection
        // $grouped_lines = DocumentLine::whereIn($this->getParentModelSnakeCase().'_id', $list)->get();

        // Initialize totals
        $total_currency_tax_incl = 0;
        $total_currency_tax_excl = 0;
        $total_tax_incl = 0;
        $total_tax_excl = 0;

        // Initialize line sort order
        $i = 0;

        foreach ($documents as $document) {
            # code...
            $i++;

            // Text line announces Shipping Slip
            $line_data = [
                'line_sort_order' => $i*10, 
                'line_type' => 'comment', 
                'name' => l('Shipping Slip: :id [:date]', ['id' => $document->document_reference, 'date' => abi_date_short($document->document_date)]),
//                'product_id' => , 
//                'combination_id' => , 
                'reference' => $document->document_reference, 
//                'name', 
                'quantity' => 1, 
                'measure_unit_id' => Configuration::getInt('DEF_MEASURE_UNIT_FOR_PRODUCTS'),
//                    'cost_price', 'unit_price', 'unit_customer_price', 
//                    'prices_entered_with_tax',
//                    'unit_customer_final_price', 'unit_customer_final_price_tax_inc', 
//                    'unit_final_price', 'unit_final_price_tax_inc', 
//                    'sales_equalization', 'discount_percent', 'discount_amount_tax_incl', 'discount_amount_tax_excl', 
                'total_tax_incl' => 0, 
                'total_tax_excl' => 0, 
//                    'tax_percent', 'commission_percent', 
                'notes' => '', 
                'locked' => 0,
 //                 'customer_shipping_slip_id',
                'tax_id' => Configuration::get('DEF_TAX'),  // Just convenient
 //               'sales_rep_id'
            ];

            $invoice_line = CustomerInvoiceLine::create( $line_data );

            $invoice->lines()->save($invoice_line);

            // Add current Shipping Slip lines to Invoice
            foreach ($document->lines as $line) {
                # code...
                $i++;

                // $invoice_line = $line->toInvoiceLine();

                // Common data
                $data = [
                ];

                $data = $line->toArray();
                // id
                unset( $data['id'] );
                // Parent document
                unset( $data[$this->getParentModelSnakeCase().'_id'] );
                // Dates
                unset( $data['created_at'] );
                unset( $data['deleted_at'] );
                // linetaxes
                unset( $data['linetaxes'] );
                // Sort order
                $data['line_sort_order'] = $i*10; 
                // Locked 
                $data['locked'] = 1; 

                // Model specific data
                $extradata = [
                ];

                // abi_r($this->getParentModelSnakeCase().'_id');
                // abi_r($data, true);


                // Let's get dirty
                CustomerInvoiceLine::unguard();
                $invoice_line = CustomerInvoiceLine::create( $data + $extradata );
                CustomerInvoiceLine::reguard();

                $invoice->lines()->save($invoice_line);

                foreach ($line->taxes as $linetax) {

                    // $invoice_line_tax = $this->lineTaxToInvoiceLineTax( $linetax );
                    // Common data
                    $data = [
                    ];

                    $data = $linetax->toArray();
                    // id
                    unset( $data['id'] );
                    // Parent document
                    unset( $data[$this->getParentModelSnakeCase().'_line_id'] );
                    // Dates
                    unset( $data['created_at'] );
                    unset( $data['deleted_at'] );

                    // Model specific data
                    $extradata = [
                    ];


                    // Let's get dirty
                    CustomerInvoiceLineTax::unguard();
                    $invoice_line_tax = CustomerInvoiceLineTax::create( $data + $extradata );
                    CustomerInvoiceLineTax::reguard();

                    $invoice_line->taxes()->save($invoice_line_tax);

                }
            }

            // Not so fast, Sony Boy

            // Confirm Invoice
            $document->confirm();

            // Close Invoice
            $document->close();


            // Document traceability
            //     leftable  is this document
            //     rightable is Customer Invoice Document
            $link_data = [
                'leftable_id'    => $document->id,
                'leftable_type'  => $document->getClassName(),

                'rightable_id'   => $invoice->id,
                'rightable_type' => CustomerInvoice::class,

                'type' => 'traceability',
                ];

            $link = DocumentAscription::create( $link_data );
        }

        // Good boy, so far




        // abi_r($grouped_lines, true);



        return redirect('customerinvoices/'.$document->id.'/edit')
                ->with('success', l('This record has been successfully created &#58&#58 (:id) ', ['id' => $document->id], 'layouts'));





//        3.- Si algún documento tiene plantilla diferente, generar factura para él <= Tontá: el albarán NO tiene plantilla de Factura

//        6.- Crear línea de texto con los albaranes ???

//        7.- Crear líneas agrupadas ???

//        8.- Manage estados de documento, pago y stock
    }





    public function getDocumentAvailability($id, Request $request)
    {
        // abi_r($request->all(), true);

        $onhand_only = $request->input('onhand_only', 0);

        $document = $this->document
                        ->with('lines')
                        ->with('lines.product')
                        ->findOrFail($id);

        if ($onhand_only)
            foreach ($document->lines as $line) {
                # code...
                if ( $line->line_type == 'product' ) {
                    $quantity = $line->quantity;
                    $onhand   = $line->product->quantity_onhand > 0 ? $line->product->quantity_onhand : 0;
    
                    $line->quantity_onhand =  $quantity > $onhand ? $onhand : $quantity;

                } else {
                    $line->quantity_onhand = $line->quantity;

                }
            }
        else
            foreach ($document->lines as $line) {
                # code...

                $line->quantity_onhand = $line->quantity;
            }

        $sequenceList = Sequence::listFor( 'App\\CustomerOrder' );

        $templateList = Template::listFor( 'App\\CustomerOrder' );

        return view($this->view_path.'._panel_document_availability', $this->modelVars() + compact('document', 'sequenceList', 'templateList', 'onhand_only'));
    }


    public function createSingleOrder( Request $request )
    {
        $document = $this->document
                            ->with('customer')
                            ->with('currency')
                            ->findOrFail( $request->input('document_id') );
        
        $customer = $document->customer;
        
        // Dates (cuen)
        $this->mergeFormDates( ['order_date'], $request );

        $rules = $this->document::$rules_createorder;

        $this->validate($request, $rules);

        // Header
        $shipping_method_id = $document->shipping_method_id ?? 
                              $customer->getShippingMethodId();

        $shipping_method = ShippingMethod::find($shipping_method_id);
        $carrier_id = $shipping_method ? $shipping_method->carrier_id : null;

        // Common data
        $data = [
//            'company_id' => $this->company_id,
            'customer_id' => $customer->id,
//            'user_id' => $this->,

            'sequence_id' => $request->input('order_sequence_id') ?? Configuration::getInt('DEF_CUSTOMER_ORDER_SEQUENCE'),

            'created_via' => 'quotation',

            'document_date' => $request->input('order_date') ?? \Carbon\Carbon::now(),

            'currency_conversion_rate' => $document->currency->conversion_rate,
//            'down_payment' => $this->down_payment,

            'document_discount_percent' => $document->document_discount_percent,
            'document_ppd_percent'      => $document->document_ppd_percent,

//            'total_currency_tax_incl' => $document->total_currency_tax_incl,
//            'total_currency_tax_excl' => $document->total_currency_tax_excl,
//            'total_currency_paid' => $this->total_currency_paid,

//            'total_tax_incl' => $document->total_tax_incl,
//            'total_tax_excl' => $document->total_tax_excl,

//            'commission_amount' => $this->commission_amount,

            // Skip notes

            'status' => 'draft',
            'onhold' => 0,
            'locked' => 0,

            'invoicing_address_id' => $document->invoicing_address_id ?? $customer->invoicing_address_id,
            'shipping_address_id' => $document->shipping_address_id ?? $customer->shipping_address_id,
            'warehouse_id' => $document->warehouse_id ?? Configuration::getInt('DEF_WAREHOUSE'),
            'shipping_method_id' => $shipping_method_id,
            'carrier_id' => $carrier_id,
            'sales_rep_id' => $document->sales_rep_id,
            'currency_id' => $document->currency->id,
            'payment_method_id' => $document->payment_method_id,
            'template_id' => $request->input('order_template_id') ?? Configuration::getInt('DEF_CUSTOMER_ORDER_TEMPLATE'),
        ];

        // Model specific data
        $extradata = [
//            'type' => 'invoice',
//            'payment_status' => 'pending',
//            'stock_status' => 'completed',
        ];


        // Let's get dirty
//        CustomerInvoice::unguard();
        $order = CustomerOrder::create( $data + $extradata );
//        CustomerInvoice::reguard();


//        5a.- Añadir Albarán
//        5b.- Crear enlaces para trazabilidad de documentos
        // Initialize grouped lines collection
        // $grouped_lines = DocumentLine::whereIn($this->getParentModelSnakeCase().'_id', $list)->get();

        // Initialize totals
        $total_currency_tax_incl = 0;
        $total_currency_tax_excl = 0;
        $total_tax_incl = 0;
        $total_tax_excl = 0;

        // Initialize line sort order
        $i = 0;

//        foreach ($documents as $document) {
            # code...
            $i++;

            // Text line announces Shipping Slip
            $line_data = [
                'line_sort_order' => $i*10, 
                'line_type' => 'comment', 
                'name' => l('Quotation: :id [:date]', ['id' => $document->document_reference, 'date' => abi_date_short($document->document_date)]),
//                'product_id' => , 
//                'combination_id' => , 
                'reference' => $document->document_reference, 
//                'name', 
                'quantity' => 1, 
                'measure_unit_id' => Configuration::getInt('DEF_MEASURE_UNIT_FOR_PRODUCTS'),
//                    'cost_price', 'unit_price', 'unit_customer_price', 
//                    'prices_entered_with_tax',
//                    'unit_customer_final_price', 'unit_customer_final_price_tax_inc', 
//                    'unit_final_price', 'unit_final_price_tax_inc', 
//                    'sales_equalization', 'discount_percent', 'discount_amount_tax_incl', 'discount_amount_tax_excl', 
                'total_tax_incl' => 0, 
                'total_tax_excl' => 0, 
//                    'tax_percent', 'commission_percent', 
                'notes' => '', 
                'locked' => 0,
 //                 'customer_shipping_slip_id',
                'tax_id' => Configuration::get('DEF_TAX'),  // Just convenient
 //               'sales_rep_id'
            ];

            $order_line = CustomerOrderLine::create( $line_data );

            $order->lines()->save($order_line);

            // Need Backorder? We'll see in a moment:
            $need_backorder = false;
            $bo_quantity    = [];       // Backorder quantity

            // Add current Shipping Slip lines to Invoice
            foreach ($document->lines as $line) {
                # code...
                $i++;

                // $order_line = $line->toInvoiceLine();

                // Common data
                $data = [
                ];

                $data = $line->toArray();
                // id
                unset( $data['id'] );
                // Parent document
                unset( $data[$this->getParentModelSnakeCase().'_id'] );
                // Dates
                unset( $data['created_at'] );
                unset( $data['deleted_at'] );
                // linetaxes
                unset( $data['linetaxes'] );
                // Sort order
                $data['line_sort_order'] = $i*10; 
                // Quantity
                // $data['quantity'] = $dispatch[$line->id];
                // Locked 
                $data['locked'] = 1; 

                // Model specific data
                $extradata = [
                ];

                // abi_r($this->getParentModelSnakeCase().'_id');
                // abi_r($data, true);


                // Let's get dirty
                CustomerOrderLine::unguard();
                $order_line = CustomerOrderLine::create( $data + $extradata );
                CustomerOrderLine::reguard();

                $order->lines()->save($order_line);
            }

            // Update lines
            $order->load(['lines']);

            foreach ($order->lines as $line) {

//                if ($line->line_type == 'comment')                   continue;

                $order->updateLine( $line->id, [ 'line_type' => $line->line_type, 'unit_customer_final_price' => $line->unit_customer_final_price ] );
            }



            // Not so fast, Sony Boy

            // Confirm Order
            $order->confirm();

            // Final touches
            $document->order_at = \Carbon\Carbon::now();
            $document->save();      // Maybe not needed, because we are to close 

            // Close Quotation
            $document->confirm();
            $document->close();


            // Document traceability
            //     leftable  is this document
            //     rightable is Customer Shipping Slip Document
            $link_data = [
                'leftable_id'    => $document->id,
                'leftable_type'  => $document->getClassName(),

                'rightable_id'   => $order->id,
                'rightable_type' => CustomerOrder::class,

                'type' => 'traceability',
                ];

            $link = DocumentAscription::create( $link_data );
//        }

        // Good boy, so far



        return redirect('customerorders/'.$order->id.'/edit')
                ->with('success', l('This record has been successfully created &#58&#58 (:id) ', ['id' => $document->id], 'layouts'));
    } 


    /*
    |--------------------------------------------------------------------------
    | Not CRUD stuff here
    |--------------------------------------------------------------------------
    */


/* ********************************************************************************************* */    


    /**
     * Return a json list of records matching the provided query
     *
     * @return json
     */
    public function ajaxLineSearch(Request $request)
    {
        // Request data
        $line_id         = $request->input('line_id');
        $product_id      = $request->input('product_id');
        $combination_id  = $request->input('combination_id', 0);
        $customer_id     = $request->input('customer_id');
        $sales_rep_id    = $request->input('sales_rep_id', 0);
        $currency_id     = $request->input('currency_id', Context::getContext()->currency->id);

//        return "$product_id, $combination_id, $customer_id, $currency_id";

        if ($combination_id>0) {
            $combination = Combination::with('product')->with('product.tax')->find(intval($combination_id));
            $product = $combination->product;
            $product->reference = $combination->reference;
            $product->name = $product->name.' | '.$combination->name;
        } else {
            $product = Product::with('tax')->find(intval($product_id));
        }

        $customer = Customer::find(intval($customer_id));

        $sales_rep = null;
        if ($sales_rep_id>0)
            $sales_rep = SalesRep::find(intval($sales_rep_id));
        if (!$sales_rep)
            $sales_rep = (object) ['id' => 0, 'commission_percent' => 0.0]; 
        
        $currency = ($currency_id == Context::getContext()->currency->id) ?
                    Context::getContext()->currency :
                    Currency::find(intval($currency_id));

        $currency->conversion_rate = $request->input('conversion_rate', $currency->conversion_rate);

        if ( !$product || !$customer || !$currency ) {
            // Die silently
            return '';
        }

        $tax = $product->tax;

        // Calculate price per $customer_id now!
        $price = $product->getPriceByCustomer( $customer, 1, $currency );
        $tax_percent = $tax->getFirstRule()->percent;
        $price->applyTaxPercent( $tax_percent );

        $data = [
//          'id' => '',
            'line_sort_order' => '',
            'line_type' => 'product',
            'product_id' => $product->id,
            'combination_id' => $combination_id,
            'reference' => $product->reference,
            'name' => $product->name,
            'quantity' => 1,
            'cost_price' => $product->cost_price,
            'unit_price' => $product->price,
            'unit_customer_price' => $price->getPrice(),
            'unit_final_price' => $price->getPrice(),
            'unit_final_price_tax_inc' => $price->getPriceWithTax(),
            'unit_net_price' => $price->getPrice(),
            'sales_equalization' => $customer->sales_equalization,
            'discount_percent' => 0.0,
            'discount_amount_tax_incl' => 0.0,
            'discount_amount_tax_excl' => 0.0,
            'total_tax_incl' => 0.0,
            'total_tax_excl' => 0.0,
            'tax_percent' => $product->as_percentable($tax_percent),
            'commission_percent' => $sales_rep->commission_percent,
            'notes' => '',
            'locked' => 0,
//          'customer_invoice_id' => '',
            'tax_id' => $product->tax_id,
            'sales_rep_id' => $sales_rep->id,
        ];

        $line = new DocumentLine( $data );

        return view($this->view_path.'._invoice_line', [ 'i' => $line_id, 'line' => $line ] );
    }


    /**
     * Return a json list of records matching the provided query
     *
     * @return json
     */
    public function ajaxLineOtherSearch(Request $request)
    {
        // Request data
        $line_id         = $request->input('line_id');
        $other_json      = $request->input('other_json');
        $customer_id     = $request->input('customer_id');
        $sales_rep_id    = $request->input('sales_rep_id', 0);
        $currency_id     = $request->input('currency_id', Context::getContext()->currency->id);

//        return "$product_id, $combination_id, $customer_id, $currency_id";

        if ($other_json) {
            $product = (object) json_decode( $other_json, true);
        } else {
            $product = $other_json;
        }

        $customer = Customer::find(intval($customer_id));

        $sales_rep = null;
        if ($sales_rep_id>0)
            $sales_rep = SalesRep::find(intval($sales_rep_id));
        if (!$sales_rep)
            $sales_rep = (object) ['id' => 0, 'commission_percent' => 0.0]; 
        
        $currency = ($currency_id == Context::getContext()->currency->id) ?
                    Context::getContext()->currency :
                    Currency::find(intval($currency_id));

        $currency->conversion_rate = $request->input('conversion_rate', $currency->conversion_rate);

        if ( !$product || !$customer || !$currency ) {
            // Die silently
            return '';
        }

        $tax = Tax::find($product->tax_id);

        // Calculate price per $customer_id now!
        $amount_is_tax_inc = Configuration::get('PRICES_ENTERED_WITH_TAX');
        $amount = $amount_is_tax_inc ? $product->price_tax_inc : $product->price;
        $price = new Price( $amount, $amount_is_tax_inc, $currency );
        $tax_percent = $tax->getFirstRule()->percent;
        $price->applyTaxPercent( $tax_percent );

        $data = [
//          'id' => '',
            'line_sort_order' => '',
            'line_type' => $product->line_type,
            'product_id' => 0,
            'combination_id' => 0,
            'reference' => DocumentLine::getTypeList()[$product->line_type],
            'name' => $product->name,
            'quantity' => 1,
            'cost_price' => $product->cost_price,
            'unit_price' => $product->price,
            'unit_customer_price' => $price->getPrice(),
            'unit_final_price' => $price->getPrice(),
            'unit_final_price_tax_inc' => $price->getPriceWithTax(),
            'unit_net_price' => $price->getPrice(),
            'sales_equalization' => $customer->sales_equalization,
            'discount_percent' => 0.0,
            'discount_amount_tax_incl' => 0.0,
            'discount_amount_tax_excl' => 0.0,
            'total_tax_incl' => 0.0,
            'total_tax_excl' => 0.0,
            'tax_percent' => $price->as_percentable($tax_percent),
            'commission_percent' => $sales_rep->commission_percent,
            'notes' => '',
            'locked' => 0,
//          'customer_invoice_id' => '',
            'tax_id' => $product->tax_id,
            'sales_rep_id' => $sales_rep->id,
        ];

        $line = new DocumentLine( $data );

        return view($this->view_path.'._invoice_line', [ 'i' => $line_id, 'line' => $line ] );
    }

}
