<?php 

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Address;
use App\Models\BankAccount;
use App\Models\Company;
use App\Models\Configuration;
use App\Models\Context;
use App\Models\Country;
use Illuminate\Http\Request;
use View;
use \iImage;

class CompaniesController extends Controller {


   protected $company;
   protected $address;

   public function __construct(Company $company, Address $address)
   {
        $this->company = $company;
        $this->address = $address;
   }
	/**
	 * Display a listing of the resource.
	 * GET /companies
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( intval(Configuration::get('DEF_COMPANY')) > 0 ) 
			return $this->edit(intval(Configuration::get('DEF_COMPANY')));
		else
			return $this->create();
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /companies/create
	 *
	 * @return Response
	 */
	public function create()
	{
 //       return View::make('companies.create');

        if ( Configuration::get('DEF_COMPANY') > 0 ) {
        	// Company already created
        	return $this->edit(intval(Configuration::get('DEF_COMPANY')));
        } else {
        
            return View::make('companies.create');
        }
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /companies
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$this->validate($request, $this->company::$rules);
		$this->validate($request, $this->address::related_rules());

		$request->merge(['notes' => $request->input('address.notes'), 'name_commercial' => $request->input('address.name_commercial')]);

		$company = $this->company->create( $request->all() );

		$data = $request->input('address');
//		$data['notes'] = '';
		$address = $this->address->create( $data );
		$company->addresses()->save($address);

		Configuration::updateValue('DEF_COMPANY', $this->company->id);

        // Create Warehouse (guess it is the same address...)
        $warehouse = $this->warehouse->create( $request->all() );

        $address = $this->address->create($data);
        $warehouse->addresses()->save($address);

        Configuration::updateValue('DEF_WAREHOUSE', $warehouse->id);


        // Set some sensible defaults!
        Configuration::updateValue('DEF_COUNTRY', $company->address->country_id);

        Configuration::updateValue('DEF_CURRENCY',  $company->currency_id);

        Configuration::updateValue('DEF_LANGUAGE', $company->id);

//        Configuration::updateValue('DEF_TAX', Tax::first()->id);

		return redirect('companies/'.$company->id.'/edit')
				->with('info', l('This record has been successfully created &#58&#58 (:id) ', ['id' => $company->id], 'layouts') . $request->input('name_fiscal'));
	}

	/**
	 * Display the specified resource.
	 * GET /companies/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /companies/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
        $company = $this->company
        					->with('address')
        					->with('currency')
        					->with('bankaccounts')
        					->findOrFail( intval($id) );

        $country = Country::find( $company->address->country_id );

        $stateList = $country ? $country->states()->pluck('name', 'id')->toArray() : [] ;
    
        return View::make('companies.edit', compact('company', 'stateList'));
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /companies/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id, Request $request)
	{
		$this->validate($request, $this->company::$rules);
		$this->validate($request, $this->address::related_rules());

		$company = $this->company->findOrFail($id);
		$address = $company->address;

		// Handle the user upload of company logo
		if ( $request->has('company_logo_file') ) {
			$file = $request->file('company_logo_file');
//			$filename = 'company_logo'.'.'.$file->getClientOriginalExtension();
			$filename = time().'.'.$file->getClientOriginalExtension();
//			iImage::make($file)->resize(300,300)->save( public_path('/uploads/company/'.$filename) );
			iImage::make($file)->save( public_path( Company::imagesPath() . $filename ) );

			// 'HEADER_TITLE' Not needed anymore
			Configuration::updateValue('HEADER_TITLE', '');

			// Delete old image
			$old_file = public_path( Company::imagesPath() . use Context::getContext()->company->company_logo );
	        if ( use Context::getContext()->company->company_logo && file_exists( $old_file ) ) {
	            unlink( $old_file );
      		}

      		$request->merge([ 'company_logo' => $filename ]);
		}

		$request->merge(['notes' => $request->input('address.notes'), 'name_commercial' => $request->input('address.name_commercial')]);
		
		$company->update(  $request->all()  );

		$data = $request->input('address');
//		$data['notes'] = '';
		$address->update( $data );

		return redirect('companies/'.$company->id.'/edit')
				->with('info', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $company->id], 'layouts') . $request->input('name_fiscal'));
	}

    /**
     * Update Bank Account in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function updateBankAccount($id, Request $request)
    {

        $section = '#bankaccounts';

//        abi_r($request->input('is_default'), true);

        $company = $this->company->with('bankaccounts')->findOrFail($id);

        $request->merge( ['notes' => $request->input('bank_account_notes')] );

        $this->validate($request, BankAccount::$rules);

        $bankaccount = $company->bankaccounts->where('id', $request->input('bank_account_id'))->first();

        if ( $bankaccount )
        {
            // Update
            $bankaccount->update($request->all());
        } else {

            // Force default bank account
            if ( $company->bankaccounts->count() == 0 )
            {
				$request->merge( ['is_default' => 1] );
            }

            // Create
            $bankaccount = BankAccount::create($request->all());
            $company->bankaccounts()->save($bankaccount);
        }

        // Bank account is default account?
        if ( $request->input('is_default') > 0 )
        {
        	$company->bank_account_id = $bankaccount->id;
            $company->save();
        }

        return redirect('companies/'.$company->id.'/edit'.$section)
            ->with('info', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $id], 'layouts') . $company->name_commercial);

    }

	/**
	 * Remove the specified resource from storage.
	 * DELETE /companies/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		// Bad idea to reach this point...
	}


    /**
     * Remove Bank Account from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroyBankAccount($company_id, $bank_account_id, Request $request)
    {

        $section = '#bankaccounts';

//        abi_r($request->input('is_default'), true);
        $company = $this->company->with('bankaccounts')->findOrFail($company_id);

        $bankaccount = $company->bankaccounts->where('id', $bank_account_id)->first();

        if ( !$bankaccount )
            abort(404);


        try {

            $bankaccount->delete();
            
        } catch (\Exception $e) {

            return redirect()->back()
                    ->with('error', l('This record cannot be deleted because it is in use &#58&#58 (:id) ', ['id' => $bankaccount->id], 'layouts').$e->getMessage());
            
        }

        

        return redirect('companies/'.$company->id.'/edit'.$section)
            ->with('info', l('This record has been successfully deleted &#58&#58 (:id) ', ['id' => $bank_account_id], 'layouts') . $company->name_commercial);
	}


/* ********************************************************************************************* */  


    /**
     * AJAX Stuff.
     *
     * 
     */


    public function getBankAccount($company_id, $bank_account_id)
    {
        $company = $this->company->with('bankaccounts')->findOrFail($company_id);

        $bankaccount = $company->bankaccounts->where('id', $bank_account_id)->first();

        if ( !$bankaccount )
            return response()->json( [] );

        // abi_r($document_line->toArray());die();

        return response()->json( $bankaccount->toArray() + ['is_default' => (int) ($bankaccount->id == $company->bank_account_id)]);
    }

}