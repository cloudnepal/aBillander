<?php

// Most suitable way to go about this is listen to db queries. You can do
/*
\DB::listen(function ($query) {
    dump($query->sql);
    dump($query->bindings);
    dump($query->time);
});
*/

/*
|--------------------------------------------------------------------------
| Gorrino Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/




/* ********************************************************** */

Route::get('tlot', function( )
{
	// abi_r(substr('z', -1), true);

	$a = null;
	$b = (string) $a;

	if ($b == '')
		echo 'OK';

	die();

	$date = \Carbon\Carbon::parse('2020-07-10 13:26:11.123789');

	$date2 = \Carbon\Carbon::parse($date)->addMonths(8);

	abi_r($date);
	abi_r($date2);

	$diff = $date2->diffInDays($date);
	abi_r($diff);

	abi_r(\App\Lot::ShortCaducity(\Carbon\Carbon::now()->subDays(20), null, '0d'));
});


/* ********************************************************** */

Route::get('cdate', function( )
{

	$date = \Carbon\Carbon::parse('2020-07-10');

	abi_r(\Carbon\Carbon::parse( \Carbon\Carbon::parse('2020-07-10') ));
});


/* ********************************************************** */

Route::get('arr', function( )
{
	abi_r(\Str::plural('child'));

	$array = [100, 200, 300];

	$first = \Arr::first($array, function ($value, $key) {
	    return $value >= 150;
	});

	abi_r($first);
});


/* ********************************************************** */

Route::get('f3', function( )
{
	$fs=[486, 205, 136];

	foreach ($fs as $fi)
	{
	    $f=\App\CustomerInvoice::find($fi);
	
	    echo $f->id.' - '.$f->open_balance.' - '.$f->payment_status;
	    
	    $f->checkPaymentStatus();
	
	    abi_r($f->open_balance);
	    abi_r($f->payment_status);
	    abi_r($f->payment_status_name);
	    abi_r('**********');
	}
});


/* ********************************************************** */


Route::get('xtra_state_id', function()
{
  // 2020-11-02
  $data = [

'535' => ['41087', '42'],
'536' => ['21600', '21'],
'295' => ['02630', '4'],
'298' => ['21730', '24'],
'43' => ['31400', '35'],
'559' => ['', '42'],
'48' => ['41927', '42'],
'51' => ['41970', '42'],
'311' => ['14100', '18'],
'58' => ['41020', '42'],
'314' => ['41003', '42'],
'587' => ['41770', '42'],
'337' => ['41310', '42'],
'117' => ['6892', '8'],
'123' => ['41370', '42'],
'388' => ['04740', '5'],
'389' => ['14700', '18'],
'392' => ['30180', '34'],
'148' => ['11405', '14'],
'193' => ['41008', '42'],
'199' => ['41111', '42'],
'207' => ['41008', '42'],
'221' => ['41018', '42'],
'232' => ['41001', '42'],
'1' => ['41920', '42'],
'10' => ['41003', '42'],
'97' => ['41001', '42'],
'98' => ['11510', '14'],
'99' => ['28021', '32'],
'100' => ['41003', '42'],
'101' => ['28521', '32'],
'102' => ['41012', '42'],
'103' => ['23700', '26'],
'104' => ['', '18'],
'105' => ['34200', '37'],
'106' => ['11630', '14'],
'11' => ['41657', '42'],
'107' => ['41012', '42'],
'108' => ['3820', '2'],
'109' => ['5003', '7'],
'110' => ['33936', '6'],
'111' => ['6011', '8'],
'112' => ['11404', '14'],
'113' => ['20400', '20'],
'114' => ['41450', '42'],
'115' => ['41657', '42'],
'116' => ['36210', '38'],
'12' => ['41002', '42'],
'304' => ['06892', '8'],
'118' => ['31008', '35'],
'119' => ['41013', '42'],
'120' => ['41001', '42'],
'121' => ['41940', '42'],
'122' => ['4410', '5'],
'302' => ['41370', '42'],
'124' => ['28100', '32'],
'125' => ['41001', '42'],
'126' => ['21620', '24'],
'13' => ['41003', '42'],
'127' => ['31500', '35'],
'128' => ['4117', '5'],
'129' => ['41001', '42'],
'130' => ['21500', '24'],
'131' => ['41010', '42'],
'132' => ['11130', '14'],
'133' => ['41020', '42'],
'134' => ['41015', '42'],
'135' => ['41002', '42'],
'14' => ['31007', '35'],
'136' => ['41005', '42'],
'137' => ['41002', '42'],
'138' => ['', '42'],
'139' => ['8720', '10'],
'140' => ['14001', '18'],
'141' => ['18007', '22'],
'142' => ['29700', '33'],
'143' => ['41011', '42'],
'144' => ['21730', '24'],
'145' => ['41220', '42'],
'15' => ['41002', '42'],
'146' => ['48346', '108'],
'150' => ['29010', '33'],
'147' => ['41940', '42'],
'313' => ['11405', '14'],
'149' => ['29631', '33'],
'296' => ['29010', '33'],
'151' => ['41700', '42'],
'152' => ['29560', '33'],
'153' => ['21002', '24'],
'154' => ['11500', '14'],
'155' => ['11540', '14'],
'16' => ['14003', '18'],
'156' => ['48991', '11'],
'157' => ['41014', '42'],
'158' => ['8295', '10'],
'159' => ['11004', '14'],
'160' => ['11150', '14'],
'161' => ['41012', '42'],
'162' => ['3201', '2'],
'163' => ['41930', '42'],
'164' => ['41300', '42'],
'17' => ['41003', '42'],
'165' => ['41450', '42'],
'166' => ['28053', '32'],
'167' => ['28670', '32'],
'168' => ['28008', '32'],
'169' => ['31014', '35'],
'170' => ['18009', '22'],
'171' => ['31580', '35'],
'172' => ['28922', '32'],
'173' => ['49337', '109'],
'18' => ['41004', '42'],
'174' => ['28008', '32'],
'175' => ['20004', '20'],
'176' => ['8040', '10'],
'177' => ['31490', '35'],
'178' => ['41014', '42'],
'179' => ['41092', '42'],
'180' => ['41008', '42'],
'181' => ['41015', '42'],
'182' => ['41219', '42'],
'183' => ['41003', '42'],
'19' => ['20006', '20'],
'184' => ['31013', '35'],
'185' => ['36210', '38'],
'186' => ['41318', '42'],
'187' => ['29713', '33'],
'188' => ['31110', '35'],
'189' => ['41310', '42'],
'190' => ['41005', '42'],
'191' => ['47400', '48'],
'192' => ['31740', '35'],
'303' => ['41008', '42'],
'2' => ['41003', '42'],
'20' => ['28044', '32'],
'194' => ['11380', '14'],
'195' => ['31770', '35'],
'196' => ['47152', '48'],
'197' => ['41219', '42'],
'198' => ['31014', '35'],
'297' => ['41111', '42'],
'200' => ['08017', '10'],
'201' => ['41710', '42'],
'202' => ['14002', '18'],
'203' => ['28003', '32'],
'21' => ['31430', '35'],
'204' => ['13195', '17'],
'205' => ['31610', '35'],
'449' => ['44643', '45'],
'305' => ['41008', '42'],
'208' => ['4720', '5'],
'209' => ['41015', '42'],
'210' => ['41001', '42'],
'211' => ['41002', '42'],
'212' => ['21100', '24'],
'213' => ['41006', '42'],
'22' => ['11402', '14'],
'214' => ['41003', '42'],
'215' => ['18800', '22'],
'216' => ['3204', '2'],
'217' => ['41980', '42'],
'218' => ['41300', '42'],
'219' => ['41013', '42'],
'220' => ['29700', '33'],
'599' => ['41018', '42'],
'222' => ['41004', '42'],
'223' => ['41020', '42'],
'23' => ['41310', '42'],
'224' => ['50008', '50'],
'225' => ['41011', '42'],
'226' => ['41310', '42'],
'227' => ['22005', '25'],
'228' => ['41005', '42'],
'229' => ['46017', '47'],
'230' => ['28033', '32'],
'231' => ['', '18'],
'315' => ['41001', '42'],
'233' => ['4617', '5'],
'24' => ['41800', '42'],
'234' => ['46909', '47'],
'235' => ['41080', '42'],
'236' => ['33530', '6'],
'237' => ['77420', '107'],
'238' => ['11100', '14'],
'239' => ['11380', '14'],
'240' => ['21730', '24'],
'241' => ['28043', '32'],
'242' => ['41701', '42'],
'243' => ['41092', '42'],
'25' => ['41520', '42'],
'244' => ['28522', '32'],
'245' => ['31110', '35'],
'246' => ['41927', '42'],
'247' => ['41002', '42'],
'248' => ['41927', '42'],
'249' => ['36360', '38'],
'250' => ['41009', '42'],
'251' => ['48930', '11'],
'252' => ['41006', '42'],
'26' => ['41003', '42'],
'253' => ['20018', '20'],
'254' => ['41013', '42'],
'255' => ['41014', '42'],
'256' => ['41310', '42'],
'450' => ['28034', '32'],
'451' => ['41710', '42'],
'452' => ['8630', '10'],
'453' => ['28020', '32'],
'454' => ['2630', '2'],
'455' => ['41600', '42'],
'27' => ['41003', '42'],
'456' => ['8017', '10'],
'457' => ['41003', '42'],
'458' => ['4867', '5'],
'459' => ['11007', '14'],
'460' => ['41410', '42'],
'461' => ['28108', '32'],
'320' => ['8720', '10'],
'322' => ['41014', '42'],
'462' => ['39300', '39'],
'28' => ['41002', '42'],
'324' => ['41020', '42'],
'463' => ['50830', '50'],
'464' => ['11004', '14'],
'465' => ['21007', '21'],
'466' => ['23004', '26'],
'467' => ['11350', '14'],
'468' => ['29200', '33'],
'469' => ['30800', '34'],
'470' => ['04230', '5'],
'471' => ['41740', '42'],
'29' => ['41003', '42'],
'472' => ['21120', '21'],
'473' => ['41003', '42'],
'474' => ['14002', '14'],
'475' => ['11311', '14'],
'476' => ['34002', '34'],
'477' => ['8202', '10'],
'478' => ['41927', '42'],
'479' => ['41020', '42'],
'480' => ['41927', '42'],
'481' => ['41011', '42'],
'3' => ['41310', '42'],
'30' => ['41927', '42'],
'482' => ['41520', '42'],
'483' => ['6011', '8'],
'484' => ['43820', '44'],
'485' => ['41007', '42'],
'486' => ['41020', '42'],
'487' => ['41500', '42'],
'488' => ['20012', '20'],
'489' => ['6800', '8'],
'490' => ['14700', '14'],
'491' => ['41020', '42'],
'31' => ['41009', '42'],
'492' => ['41390', '42'],
'493' => ['11500', '14'],
'494' => ['28001', '32'],
'495' => ['41520', '42'],
'496' => ['41720', '42'],
'447' => ['41720', '42'],
'497' => ['30002', '34'],
'498' => ['41001', '42'],
'499' => ['11010', '14'],
'500' => ['28046', '32'],
'32' => ['45001', '46'],
'501' => ['41003', '42'],
'502' => ['41720', '42'],
'503' => ['11130', '14'],
'504' => ['41008', '42'],
'505' => ['50583', '50'],
'506' => ['41310', '42'],
'507' => ['18003', '22'],
'448' => ['41012', '42'],
'508' => ['41003', '42'],
'509' => ['41008', '42'],
'33' => ['41008', '42'],
'510' => ['41530', '42'],
'511' => ['41020', '42'],
'512' => ['41001', '42'],
'513' => ['41927', '42'],
'514' => ['41720', '42'],
'515' => ['34002', '37'],
'516' => ['28046', '32'],
'517' => ['28016', '32'],
'518' => ['41807', '42'],
'519' => ['11010', '14'],
'34' => ['28042', '32'],
'520' => ['30880', '34'],
'521' => ['41012', '42'],
'522' => ['5002', '7'],
'583' => ['10694', '13'],
'602' => ['28220', '32'],
'603' => ['46192', '47'],
'35' => ['41012', '42'],
'36' => ['41318', '42'],
'37' => ['8206', '10'],
'38' => ['41310', '42'],
'4' => ['41003', '42'],
'39' => ['21410', '24'],
'40' => ['41004', '42'],
'41' => ['21600', '24'],
'42' => ['11406', '14'],
'310' => ['31400', '35'],
'44' => ['29560', '33'],
'45' => ['41013', '42'],
'46' => ['41740', '42'],
'47' => ['41807', '42'],
'293' => ['41927', '42'],
'5' => ['41002', '42'],
'49' => ['21230', '24'],
'301' => ['44564', '45'],
'258' => ['41013', '42'],
'259' => ['41020', '42'],
'260' => ['8150', '10'],
'261' => ['41005', '42'],
'262' => ['41980', '42'],
'263' => ['21500', '24'],
'264' => ['8201', '10'],
'265' => ['41013', '42'],
'266' => ['29630', '33'],
'267' => ['41900', '42'],
'268' => ['6001', '8'],
'269' => ['41980', '42'],
'270' => ['41007', '42'],
'271' => ['21230', '24'],
'272' => ['14700', '18'],
'273' => ['41309', '42'],
'274' => ['21003', '24'],
'275' => ['31110', '35'],
'276' => ['41018', '42'],
'277' => ['41020', '42'],
'278' => ['41130', '42'],
'279' => ['8830', '10'],
'280' => ['41020', '42'],
'281' => ['33860', '6'],
'282' => ['29793', '33'],
'283' => ['06007', '8'],
'284' => ['41018', '42'],
'285' => ['41004', '42'],
'286' => ['8008', '10'],
'287' => ['11300', '14'],
'288' => ['41011', '42'],
'289' => ['41008', '42'],
'290' => ['41219', '42'],
'291' => ['14900', '18'],
'292' => ['8960', '10'],
'294' => ['41807', '42'],
'523' => ['6007', '8'],
'306' => ['08017', '10'],
'312' => ['41089', '42'],
'316' => ['41300', '42'],
'323' => ['11630', '14'],
'317' => ['45516', '46'],
'524' => ['28007', '32'],
'525' => ['41807', '42'],
'526' => ['41111', '42'],
'527' => ['41970', '42'],
'528' => ['41370', '42'],
'308' => ['41500', '42'],
'529' => ['41020', '42'],
'530' => ['31400', '35'],
'531' => ['14100', '14'],
'532' => ['41001', '42'],
'533' => ['41300', '42'],
'318' => ['08213', '10'],
'325' => ['11007', '14'],
'326' => ['41600', '42'],
'327' => ['28042', '32'],
'328' => ['25700', '30'],
'329' => ['8030', '10'],
'330' => ['28007', '32'],
'331' => ['41530', '42'],
'332' => ['11520', '14'],
'333' => ['41004', '42'],
'334' => ['41018', '42'],
'335' => ['29660', '33'],
'336' => ['50570', '50'],
'338' => ['8030', '10'],
'339' => ['41005', '42'],
'340' => ['8820', '10'],
'341' => ['14900', '18'],
'342' => ['41007', '42'],
'343' => ['29610', '33'],
'344' => ['18102', '22'],
'345' => ['21830', '24'],
'346' => ['41013', '42'],
'347' => ['41023', '42'],
'348' => ['46930', '47'],
'349' => ['41006', '42'],
'350' => ['41018', '42'],
'351' => ['14011', '18'],
'352' => ['11550', '14'],
'353' => ['50583', '50'],
'354' => ['41015', '42'],
'355' => ['8191', '10'],
'356' => ['6340', '8'],
'357' => ['11610', '14'],
'358' => ['10616', '13'],
'359' => ['41710', '42'],
'360' => ['11406', '14'],
'361' => ['41012', '42'],
'362' => ['41800', '42'],
'363' => ['41003', '42'],
'364' => ['41710', '42'],
'365' => ['21120', '24'],
'366' => ['41130', '42'],
'367' => ['12170', '16'],
'368' => ['11130', '14'],
'369' => ['41500', '42'],
'370' => ['41003', '42'],
'371' => ['41479', '42'],
'372' => ['15189', '1'],
'373' => ['41980', '42'],
'374' => ['28270', '32'],
'375' => ['41420', '42'],
'376' => ['11405', '14'],
'377' => ['21459', '24'],
'378' => ['41005', '42'],
'379' => ['28002', '32'],
'380' => ['43005', '44'],
'381' => ['50650', '50'],
'382' => ['41800', '42'],
'383' => ['6340', '8'],
'384' => ['41510', '42'],
'385' => ['11500', '14'],
'386' => ['41110', '42'],
'387' => ['29680', '33'],
'534' => ['14857', '14'],
'391' => ['41018', '42'],
'390' => ['41004', '42'],
'393' => ['21600', '24'],
'537' => ['41018', '42'],
'395' => ['41087', '42'],
'396' => ['41020', '42'],
'397' => ['21001', '24'],
'398' => ['41020', '42'],
'399' => ['41012', '42'],
'400' => ['41007', '42'],
'401' => ['21001', '42'],
'402' => ['41702', '42'],
'403' => ['41002', '42'],
'404' => ['36860', '38'],
'405' => ['41820', '42'],
'406' => ['31119', '35'],
'407' => ['41008', '42'],
'408' => ['41001', '42'],
'409' => ['21001', '24'],
'410' => ['29530', '33'],
'411' => ['41500', '42'],
'412' => ['41930', '42'],
'413' => ['11130', '14'],
'414' => ['41003', '42'],
'415' => ['6800', '8'],
'416' => ['46800', '47'],
'417' => ['41011', '42'],
'418' => ['50700', '50'],
'419' => ['41970', '42'],
'420' => ['41003', '42'],
'421' => ['41015', '42'],
'422' => ['31740', '35'],
'423' => ['41930', '42'],
'424' => ['3710', '2'],
'425' => ['30319', '34'],
'426' => ['41007', '42'],
'427' => ['33003', '6'],
'428' => ['8860', '10'],
'429' => ['41500', '42'],
'430' => ['41310', '42'],
'431' => ['28005', '32'],
'432' => ['10500', '13'],
'433' => ['33460', '6'],
'434' => ['41500', '42'],
'435' => ['41300', '42'],
'436' => ['41120', '42'],
'437' => ['41003', '42'],
'438' => ['41004', '42'],
'439' => ['3502', '2'],
'440' => ['41002', '42'],
'441' => ['41007', '42'],
'442' => ['8003', '10'],
'443' => ['41001', '42'],
'444' => ['41120', '42'],
'445' => ['41520', '42'],
'446' => ['21001', '24'],
'540' => ['11600', '14'],
'542' => ['29004', '33'],
'543' => ['46870', '47'],
'544' => ['18008', '22'],
'545' => ['41900', '42'],
'546' => ['41930', '42'],
'548' => ['18151', '22'],
'549' => ['46980', '47'],
'550' => ['41569', '42'],
'552' => ['29692', '33'],
'553' => ['28224', '32'],
'554' => ['41004', '42'],
'555' => ['41800', '42'],
'556' => ['29740', '33'],
'557' => ['41003', '42'],
'558' => ['41003', '42'],
'560' => ['41010', '42'],
'561' => ['41980', '42'],
'562' => ['41927', '42'],
'563' => ['41013', '42'],
'564' => ['41005', '42'],
'565' => ['41006', '42'],
'566' => ['41018', '42'],
'567' => ['41006', '42'],
'568' => ['41013', '42'],
'569' => ['06160', '8'],
'570' => ['41010', '42'],
'571' => ['41770', '42'],
'572' => ['41130', '42'],
'573' => ['21700', '24'],
'574' => ['34005', '37'],
'575' => ['08710', '10'],
'576' => ['41310', '42'],
'577' => ['41015', '42'],
'578' => ['21730', '24'],
'579' => ['41318', '42'],
'580' => ['41010', '42'],
'581' => ['28290', '32'],
'582' => ['41013', '42'],
'584' => ['41110', '42'],
'586' => ['41003', '42'],
'588' => ['41500', '42'],
'589' => ['41007', '42'],
'590' => ['41910', '42'],
'591' => ['08019', '10'],
'592' => ['41770', '42'],
'593' => ['41940', '42'],
'594' => ['41309', '42'],
'595' => ['04008', '5'],
'598' => ['26005', '27'],
'600' => ['08812', '10'],
'601' => ['41770', '42'],
'50' => ['8221', '10'],
'299' => ['41970', '42'],
'52' => ['41008', '42'],
'53' => ['41013', '42'],
'54' => ['41219', '42'],
'55' => ['41520', '42'],
'56' => ['31016', '35'],
'57' => ['29560', '33'],
'309' => ['41020', '42'],
'6' => ['41018', '42'],
'59' => ['47005', '48'],
'605' => ['11380', '14'],
'607' => ['41849', '42'],
'608' => ['41370', '42'],
'609' => ['08203', '10'],
'610' => ['29010', '33'],
'611' => ['41940', '42'],
'615' => ['41012', '42'],
'616' => ['08034', '10'],
'617' => ['41120', '42'],
'618' => ['08757', '10'],
'619' => ['28805', '32'],
'621' => ['41704', '42'],
'622' => ['43820', '44'],
'624' => ['21500', '24'],
'625' => ['11130', '14'],
'626' => ['06178', '8'],
'627' => ['41005', '42'],
'628' => ['41720', '42'],
'629' => ['28051', '32'],
'630' => ['41900', '42'],
'631' => ['41927', '42'],
'632' => ['41003', '42'],
'633' => ['41449', '42'],
'634' => ['29649', '33'],
'637' => ['41330', '42'],
'639' => ['41310', '42'],
'60' => ['41568', '42'],
'61' => ['28229', '32'],
'62' => ['41970', '42'],
'63' => ['28660', '32'],
'64' => ['11630', '14'],
'65' => ['41010', '42'],
'66' => ['41018', '42'],
'67' => ['41013', '42'],
'7' => ['41005', '42'],
'68' => ['41013', '42'],
'69' => ['14004', '18'],
'70' => ['41530', '42'],
'71' => ['41005', '42'],
'72' => ['11391', '14'],
'73' => ['41003', '42'],
'74' => ['38206', '40'],
'75' => ['41310', '42'],
'76' => ['41710', '42'],
'77' => ['41370', '42'],
'8' => ['41005', '42'],
'78' => ['31001', '35'],
'307' => ['41310', '42'],
'319' => ['23004', '26'],
'321' => ['', '42'],
'538' => ['18183', '18'],
'539' => ['28027', '32'],
'541' => ['03202', '2'],
'547' => ['41003', '42'],
'551' => ['41003', '42'],
'585' => ['41440', '42'],
'596' => ['8375-021', '110'],
'597' => ['419127', '42'],
'606' => ['41020', '42'],
'612' => ['23008', '26'],
'613' => ['18008', '22'],
'614' => ['02510', '4'],
'620' => ['02430', '4'],
'623' => ['41004', '42'],
'635' => ['41410', '42'],
'636' => ['41300', '42'],
'638' => ['41002', '42'],
'79' => ['41011', '42'],
'80' => ['41300', '42'],
'81' => ['14700', '18'],
'82' => ['41380', '42'],
'83' => ['41018', '42'],
'84' => ['23456', '42'],
'85' => ['41002', '42'],
'86' => ['41013', '42'],
'87' => ['41003', '42'],
'9' => ['41002', '42'],
'88' => ['41230', '42'],
'89' => ['31001', '35'],
'90' => ['41440', '42'],
'91' => ['45002', '46'],
'92' => ['31004', '35'],
'93' => ['41008', '42'],
'94' => ['21730', '24'],
'95' => ['11150', '14'],
'96' => ['11404', '14'],

  ];

$customers = \App\Customer::with('address')->get();

  foreach ($customers as $customer) {
    # code...
    $addr = $customer->address;

    if ( array_key_exists($customer->id, $data))
    {
        //
        $stub = $data[$customer->id];

        if ( $addr->postcode == $stub['0'])
        {
            //
            $addr->state_id == $stub['1'];
            $addr->save();

        } else {
            //
            abi_r('Post Code mismatch: '.$customer->name_regular);

        }        

    } else {
        //
        abi_r('Not found: '.$customer->name_regular);
    }
  }


  die('OK');

});




/* ********************************************************** */


Route::get('xtra_addrs', function()
{
  // 2020-05-31
  $addrs = \App\Address::get();


  foreach ($addrs as $addr) {
    # code...
    if ( $addr->email != trim($addr->email) ){
      abi_r( '*'.$addr->email .'* - '. trim($addr->email) );
      $addr->email = trim($addr->email);
      $addr->save();
    }
  }


  die('OK');

});


/* ********************************************************** */


Route::get('stid', function()
{


	if (file_exists(__DIR__.'/gorrino_routes_adata.php')) {
	    // $cdata
	    include __DIR__.'/gorrino_routes_adata.php';
	}

	// Customers
	$ads =\App\Address::where('addressable_type', 'App\Customer')->get();

	foreach ($adata as $key => $value) {
		# code...

		$c = $ads->where('id', $key)->first();

		$c->update(['state_id' => $value]);
		echo $key.' - '.$c->id.' :: '.$value.' - '.$c->state_id.'<br />';

	}


	die('OK');


	$ads =\App\Address::select('id', 'state_id')->where('addressable_type', 'App\Customer')->get();

	
	echo '$adata = [<br />';
	
	foreach ($ads as $ad) {
		# code...
		echo ' \''.$ad->id.'\''.' => '.'\''.$ad->state_id.'\''.',<br />';
	}

	echo '];<br />';

});




Route::get('wsid', function()
{

// laravel find duplicate records
// https://stackoverflow.com/questions/40888168/use-laravel-collection-to-get-duplicate-values
// https://laracasts.com/discuss/channels/general-discussion/finding-duplicate-data


	if (file_exists(__DIR__.'/gorrino_routes_cdata.php')) {
	    // $cdata
	    include __DIR__.'/gorrino_routes_cdata.php';
	}

	// Customers
	$cws=\App\Customer::get();

	foreach ($cdata as $key => $value) {
		# code...

		$c = $cws->where('id', $key)->first();

		$c->update(['webshop_id' => $value]);
		echo $key.' - '.$c->id.' :: '.$value.' - '.$c->webshop_id.'<br />';

	}


	die('OK');

	// 2020-01-16 Get raw data
	$cs=\App\Customer::select('id', 'reference_external', 'webshop_id')
						->orderBy('reference_external')
						->get();

	
	echo '$cdata = [<br />';
	
	foreach ($cs as $c) {
		# code...
		echo ' \''.$c->id.'\''.' => '.($c->webshop_id ? ('\''.$c->webshop_id.'\'') : 'null').',<br />';
	}

	echo '];<br />';

});


/* ********************************************************** */


/* ********************************************************** */


Route::get('migratethis_xtra', function()
{
  
  // 2021-04-27
	
	Illuminate\Support\Facades\DB::statement("ALTER TABLE `products` ADD `lot_policy` varchar(32) NOT NULL DEFAULT 'FIFO' AFTER `expiry_time`;");
	
	Illuminate\Support\Facades\DB::statement("ALTER TABLE `products` ADD `lot_number_generator` VARCHAR(64) NOT NULL DEFAULT 'Default' AFTER `expiry_time`;");
	
	Illuminate\Support\Facades\DB::statement("ALTER TABLE `products` CHANGE `expiry_time` `expiry_time` VARCHAR(16) NULL DEFAULT NULL; ");

  die('OK');


  // 2020-07-09
  Illuminate\Support\Facades\DB::statement("INSERT INTO `templates` ( `name`, `model_name`, `folder`, `file_name`, `paper`, `orientation`, `created_at`, `updated_at`, `deleted_at`) VALUES
( 'xtranat Albaranes', 'CustomerShippingSlipPdf', 'templates::', 'xtranat', 'A4', 'portrait', '2020-07-09 07:30:53', '2020-07-09 07:30:53', NULL);");

  $template = \App\Template::where('file_name', 'xtranat')->where('model_name', 'CustomerShippingSlipPdf')->first();

  \App\Configuration::updateValue('DEF_CUSTOMER_SHIPPING_SLIP_TEMPLATE', $template->id);


  die('OK');

  // 2020-05-26
  Illuminate\Support\Facades\DB::statement("ALTER TABLE `customer_invoice_lines` ADD `customer_shipping_slip_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `customer_invoice_id`;");


  // 2020-05-25

  // $table->string('shipment_service_type_tag', 32)->nullable();
  
  // Illuminate\Support\Facades\DB::statement("ALTER TABLE `customer_shipping_slips` ADD `shipment_service_type_tag` varchar(32) NULL DEFAULT NULL AFTER `shipment_status`;");
  
  Illuminate\Support\Facades\DB::statement("ALTER TABLE `customers` ADD `is_invoiceable` INT(10) UNSIGNED NOT NULL DEFAULT '1' AFTER `customer_logo`;");
  
  Illuminate\Support\Facades\DB::statement("ALTER TABLE `customer_shipping_slips` ADD `is_invoiceable` INT(10) UNSIGNED NOT NULL DEFAULT '1' AFTER `shipment_service_type_tag`;");



  // 2020-05-22
    Illuminate\Support\Facades\DB::statement("ALTER TABLE `customer_invoices` ADD `production_sheet_id` INT(10) UNSIGNED NULL AFTER `posted_at`;");


  die('OK');


  // 2020-03-11
  \App\Configuration::updateValue('ABCC_OUT_OF_STOCK_PRODUCTS_NOTIFY', '0');

  die('OK');


  // 2020-03-02
  Illuminate\Support\Facades\DB::statement("ALTER TABLE `cart_lines` ADD `pmu_label` varchar(128) null AFTER `pmu_conversion_rate`;");

  $tables = ['customer_invoice', 'customer_shipping_slip', 'customer_quotation', 'customer_order'];

  foreach ($tables as $table) {
    # code...
    Illuminate\Support\Facades\DB::statement("ALTER TABLE `".$table."_lines` ADD `pmu_label` varchar(128) null AFTER `pmu_conversion_rate`;");

  }

  Illuminate\Support\Facades\DB::statement("ALTER TABLE `customer_orders` ADD `onhold` TINYINT(4) NOT NULL DEFAULT '0' AFTER `status`;");




  Illuminate\Support\Facades\DB::statement("CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

  Illuminate\Support\Facades\DB::statement("ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);
");

  Illuminate\Support\Facades\DB::statement("ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
  ");
  


  Illuminate\Support\Facades\DB::statement("CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

  Illuminate\Support\Facades\DB::statement("ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);
");

  Illuminate\Support\Facades\DB::statement("ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
  ");


    Illuminate\Support\Facades\DB::statement("ALTER TABLE `measure_units` CHANGE `conversion_rate` `type_conversion_rate` DECIMAL(20,6) NOT NULL DEFAULT '1.000000';");

    
    Illuminate\Support\Facades\DB::statement("ALTER TABLE `products` ADD `position` INT(10) NOT NULL DEFAULT '0' AFTER `name`;");



  die('OK');

});


/* ********************************************************** */

