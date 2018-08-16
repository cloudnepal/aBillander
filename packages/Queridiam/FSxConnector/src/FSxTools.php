<?php 

namespace Queridiam\FSxConnector;

use \App\Configuration;

class FSxTools
{

	/** @var array Connections.fsx-bbdd cache */
	protected static $_FSXCON;

    public static  $gates = NULL;


	public static function setFSxConnection()
	{
		self::$_FSXCON = [];


        // Get Configurations from FactuSOL Web
        if (config('app.url') =='http://abimfg.laextranatural.es' 
         || config('app.url') =='http://abimfg-test.laextranatural.es')
        {
                self::$_FSXCON['fsx-bbdd'] = 
                [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST', 'localhost'),
                    'port' => env('DB_PORT', '3306'),
                    'database' => env('DB_DATABASE_FSX', 'laextran_com'),
                    'username' => env('DB_USERNAME_FSX', 'laextran_com'),
                    'password' => env('DB_PASSWORD_FSX', 'DAS#6XqwyK%z'),
                    'unix_socket' => env('DB_SOCKET', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
        //            'strict' => true,
                    'strict' => false,
                    'engine' => null,
                ];
        } else {
                self::$_FSXCON['fsx-bbdd'] = 
                [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST', '127.0.0.1'),
                    'port' => env('DB_PORT', '3306'),
                    'database' => env('DB_DATABASE_FSX', 'wooc_btester'),
                    'username' => env('DB_USERNAME_FSX', 'root'),
                    'password' => env('DB_PASSWORD_FSX', '1qaz2wsx'),
                    'unix_socket' => env('DB_SOCKET', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
        //            'strict' => true,
                    'strict' => false,
                    'engine' => null,
                ];
        }

		\Config::set("database.connections.fsx-bbdd", self::$_FSXCON['fsx-bbdd'] );
	}

	/**
	  * Get a single configuration value
	  */
	public static function getFormasDePagoList()
	{
		// Force use cache
        if ( 1 )
        {
            // Payment Methods Cache
            $cache = Configuration::get('FSX_FORMAS_DE_PAGO_CACHE');

            $fpas = json_decode( $cache , true);
            ksort($fpas);

            return $fpas;
        }
        // See comments by the end of this file


        if (!self::$_FSXCON)
		{
			FSxTools::setFSxConnection();
		}


        // Start Logic Probe, now!
        try {

            // 'TABLA_FORMAS_PAGO'
            $formasp = \DB::connection('fsx-bbdd')->select('select `CODFPA` as id, `DESFPA` as description from `F_FPA` order by `DESFPA`');
        }

        catch( \Exception $e ) {

            return redirect()->route('fsxconfigurationkeys.index')
                    ->with('error', $e->getMessage());

        }
		
		return collect($formasp)->pluck('description', 'id')->toArray();
		
		// return collect($formasp)->map(function($x){ return (array) $x; })->toArray();
	}
    
    public static function getCodigoFormaDePago( $paymentm_id = '' )
    {
        if (!$paymentm_id) return null;

        // Dictionary
        if ( !isset(self::$gates) )
            self::$gates = json_decode(\App\Configuration::get('FSX_FORMAS_DE_PAGO_DICTIONARY_CACHE'), true);

        $gates = self::$gates;

        return isset($gates[$paymentm_id]) ? $gates[$paymentm_id] : null;
    }


/* ********************************************************************************************* */

	/**
	  * Get a single configuration value
	  */
	public static function getTipoIVA( $tax_id = null ) 
	{

		switch ($tax_id){
	            case Configuration::get('FSOL_IMPUESTO_DIRECTO_TIPO_1'):
	                  return 0;
	                  break;
	            case Configuration::get('FSOL_IMPUESTO_DIRECTO_TIPO_2'):
	                  return 1;
	                  break;
	            case Configuration::get('FSOL_IMPUESTO_DIRECTO_TIPO_3'):
	                  return 2;
	                  break; 
	            case Configuration::get('FSOL_IMPUESTO_DIRECTO_TIPO_4'):
	                  return 3;         // Exento
	                  break; 
	            default:
	                  return -1;
	                  break; 
		}   

	}


/* ********************************************************************************************* */



    public static function getPaymentMethodKey( $id = '' )
    {
            return 'FSX_PAYMENT_METHOD_'.strtoupper($id);
    }


}




/* ********************************************************** * /


Route::get('fpago', function()
{
    // aBillander Methods
    $pgatesList = \App\PaymentMethod::select('id', 'name')->orderby('name', 'desc')->get()->toArray();

    $l= [];

    foreach($pgatesList as $k => $v)
    {
        $l[] = 
                [
                    'id' => '00'.$v['id'],
                    'name' => $v['name']
                ];
    }

    $ll =collect($l)->pluck('name', 'id')->toArray();

    \App\Configuration::updateValue('FSX_FORMAS_DE_PAGO_CACHE', json_encode($ll));



    abi_r(  \App\Configuration::get('FSX_FORMAS_DE_PAGO_CACHE') );

    $fsolpaymethods = \Queridiam\FSxConnector\FSxTools::getFormasDePagoList();
    abi_r( ($fsolpaymethods ) );

});


/ * ********************************************************** */