<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\Meals;
use App\Days;
use App\Daymeals;
use App\Macros;
use JWTAuth;
use Log;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
class CaloriesController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

	  public function __construct( Request $request)
    {       
        $this->request = $request;
    }
	public function index()
	{
		//
		//$time = User::getDBinfo();
        return $time;
	}

	public function dbresult()
    {
    	$users = User::all()->toJson();
    	$token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        return [
            'data' => [
            'users' => $users,
            'user1'=>$user
            ]
        ];
    }

    public function getMacroSummary() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $dataObj = [];
                $fatsofar = 0;
                $protsofar = 0;
                $carbsofar = 0;
                $kcalsofar = 0;

                $kcallimit=0;
                $protlimit=0;
                $carblimit=0;
                $fatlimit=0;

                $currentmacro = Macros::where('current',1)->where('user_id',$user->id)->first();
                if ($currentmacro!==null) {
                    $startdate = $currentmacro->startdate;
                if ($currentmacro->bulking==1) {
                    $bulking="Bulking";
                }
                else {
                    $bulking="Cutting";
                }
                $dataObj = [
                    "protsofar"=>$protsofar,
                    "protlimit" => $protlimit,
                    "kcsofar" => $kcalsofar,
                    "kclimit" =>$kcallimit,
                    "fatsofar" => $fatsofar,
                    "fatlimit" => $fatlimit,
                    "carbsofar" => $carbsofar,
                    "carblimit" => $carblimit,
                    "thedate" => $currentmacro->startdate,
                    "bulking" => $bulking
                ];
                  //  $days = Days::where('date','>',$startdate)->where('macro_id',$currentmacro->id);
                $days = Days::where('date','>=',$startdate)->where('macro_id',$currentmacro->id)->get();
                // add up totals for all days macros
                // add up usage for each day 
              
                $dac=0;
                foreach ($days as $day) {
        
                    if ($day->type=="rest") {
                        $fatlimit = $fatlimit + $currentmacro->fats;
                        $protlimit = $protlimit + $currentmacro->prot;
                        $carblimit = $carbliit + $currentmacro->carbs;
                        $kcallimit = $kcallimit + $currentmacro->kcals;
                    }
                    if ($day->type=="workout") {
                        $fatlimit = $fatlimit + $currentmacro->wfats;
                        $protlimit = $protlimit + $currentmacro->wprot;
                        $carblimit = $carblimit + $currentmacro->wcarbs;
                        $kcallimit = $kcallimit + $currentmacro->wkcals;
                    }
                    $day_id = $day->id;
                    $dac = $day->id;
                    $mymeals = Daymeals::where('day_id',$day_id)->get();
                    foreach ($mymeals as $mymeal) {
                        $thismeal = Meals::where('id',$mymeal->meal_id)->first();
                        $fatsofar = $fatsofar + $thismeal->fats;
                        $carbsofar = $carbsofar + $thismeal->carbs;
                        $protsofar = $protsofar + $thismeal->prot;
                        $kcalsofar = $kcalsofar + $thismeal->kcals;
                    }
                }
                // generate mealsummary
                     $dataObj = [
                     "protsofar"=>$protsofar,
                     "protlimit" => $protlimit,
                     "kcsofar" => $kcalsofar,
                     "kclimit" =>$kcallimit,
                     "fatsofar" => $fatsofar,
                     "fatlimit" => $fatlimit,
                     "carbsofar" => $carbsofar,
                     "carblimit" => $carblimit,
                     "thedate" => $currentmacro->startdate,
                     "bulking" => $bulking,
                     "dac"=>$startdate,
                     "countofdays"=>$dac,
                     "Mid"=>$currentmacro->id
                    ];
            } else {
                $dataObj = "";
            } 
        return [
            'data' => [
                'dataitem' => $dataObj,
            ]
        ];
    }

    public function summary() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

                $mymacros = Macros::where('current',1)->where('user_id',$user->id)->first();
                if ($mymacros!==null) {
                $macrosummary = $this->getMacroSummary();
                $dataObj = [
                    'macrosetup'=>$mymacros,
                    'macrosummary'=>$macrosummary
                    ];
            } else {
                $dataObj="";
            } 
        return [
            'data' => [
                'dataitem' => $dataObj,
            ]
        ];
    }

    public function foodlist() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $mycurrentmacro = "";
        $foods = Meals::where('user_id',$user->id)->get();
        $dataObj = [];
        foreach ($foods as $food) {
            $objname = $food->name;
            $objid = $food->id;
            $newobj = (object)array('name' => $objname,'id'=>$objid);
            array_push($dataObj,$newobj);
        }

        return [
            'data' => [
                'dataitem' => $dataObj,
            ]
        ];
    }

    public function todaysfoods() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $data = json_decode($_POST["json"]);
        $todaydate = $_POST["todaydate"];
        $jsDateTS = strtotime($todaydate);
        $todaydate = date('Y-m-d', $jsDateTS );
        $todaydate = $todaydate." 00:00:00";

        $todaydatecheck = Days::where('date',$todaydate)->first();
        if ($todaydatecheck==null) {
            $dataObj="empty foods";
        }
        else {
        $day_id = $todaydatecheck->id;
        $meallist = [];
        $todaysfoods = Daymeals::where('day_id',$day_id)->where('user_id',$user->id)->get();
        foreach ($todaysfoods as $meal) {
            $mealitem = Meals::where('id',$meal->meal_id)->first();
            if ($mealitem!==null) {
                $mealname = $mealitem->name;
                $mealid = $mealitem->id;
                $item = ["name"=>$mealname,"id"=>$mealid];
                array_push($meallist,$item);
            }
        }
        $dataObj = $meallist;
    }
        return [
            'data' => [
                'dataitem' => $dataObj,
            ]
        ];
    }

    public function getMacros() {
        $token = (string)JWTAuth::getToken();
        $dataObj = $token;
        $user = JWTAuth::toUser($token);
        $myMacros = $this->getBasicMacros($user);
        $defaultMacro = $this->getDefaultMacro($user);
        $dataObj = [
            'macros' => $myMacros,
            'using' => $defaultMacro
    ];
        return [
            'data' => [
                'dataitem' => $dataObj,
            ]
        ];
    }

    /**
     *
     * @param $user
     */
    public function getBasicMacros($user)
    {
        $myMacrosCount = Macros::where('user_id',$user->id)->count();
        if ($myMacrosCount==0) {
            // create macros
            $macro = new Macros;
            $macro->name="Default";
            $macro->current = 1;
            $macro->user_id = $user->id;
            $macro->save();
            $newmacro = 1;
            $mymacros = Macros::where('user_id',$user->id)->get();
            $newmacro = ['new'=>1];
            return [$mymacros,$newmacro];
        }
        else {
            $newmacro = ['new'=>0];
            $mymacros = Macros::where('user_id',$user->id)->get();
            return [$mymacros,$newmacro];
        }

    }

    /**
     *
     * @param $user
     */
    public function getDefaultMacro($user) {

      
            $myDefaultMacro = Macros::where('user_id',$user->id)->where('current',1)->first();
            return $myDefaultMacro;
        
    }

    public function changemacros() {

        $data = json_decode($_POST["json"]);
        $macrocontent = $data->selectedMacro;
        $token = (string)JWTAuth::getToken();
        $dataObj2="";
        $user = JWTAuth::toUser($token);
        $dataObj=$macrocontent;
        if ($macrocontent==null) {
            // adding a new macro
            $macro = new Macro;
            $macro->kcals = $macrocontent->kcals;
            $macro->carbs = $macrocontent->carbs;
            $macro->prot = $macrocontent->prot;
            $macro->fats = $macrocontent->fats;
            $macro->wkcals = $macrocontent->kcals;
            $macro->wcarbs = $macrocontent->wcarbs;
            $macro->wprot = $macrocontent->wprot;
            $macro->wfats = $macrocontent->wfats;
            $macro->bulking = $macrocontent->bulking;
            $macro->current = $macrocontent->current;
            $macro->name = $macrocontent->name;
            $macro->user_id = $user->id;
            $macro->save();
            $dataObj = $macro;
        }
        else { 
            // editing an existing macro
            $macro_id = $macrocontent->id;
            
                $macro = Macros::where('id',$macro_id)->where('user_id',$user->id)->first();
                if ($macro!==null) {
                $dataObj2=$macrocontent;
                $macro->kcals = $macrocontent->kcals;
                $macro->carbs = $macrocontent->carbs;
                $macro->prot = $macrocontent->prot;
                $macro->fats = $macrocontent->fats;
                $macro->wkcals = $macrocontent->wkcals;
                $macro->wcarbs = $macrocontent->wcarbs;
                $macro->wprot = $macrocontent->wprot;
                $macro->wfats = $macrocontent->wfats;
                $macro->bulking = $macrocontent->bulking;
                $macro->current = $macrocontent->current;
                $macro->name = $macrocontent->name;
                $macro->user_id = $user->id;
                $macro->save();
                $dataObj = $macro;
            } else {
                $dataObj="";
            } 
        }
        //$dataObj = "";
        return [
            'data' => [
                'dataitem' => $dataObj2,
            ]
        ];
    }

    public function changebulk() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $data = json_decode($_POST["json"]);
        $bulking = $data;
        if ($bulking=="Bulking") {
            $bulking=1;
        }
        else {
            $bulking=0;
        }
        $mymacro = Macros::where('user_id',$user->id)->where('current',1)->first();
        $mymacro->bulking = $bulking;
        $mymacro->save();
        $dataObj = $mymacro;

        return [
            'data' => [
                'dataitem' => $dataObj,
            ]
        ];
    }

    public function changeworkout() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $data = json_decode($_POST["json"]);
        $todaydate = $_POST["todaydate"];
        $jsDateTS = strtotime($todaydate);
        $todaydate = date('Y-m-d', $jsDateTS );
        $todaydate = $todaydate." 00:00:00";
        $mymacro = Macros::where('user_id',$user->id)->where('current',1)->first();
        $mymacro_id = $mymacro->id;
        $daychosen = Days::where('date',$todaydate)->where('macro_id',$mymacro_id)->first();
        $day_type = $data->workoutType;
        $daychosen->type = $day_type;
        $daychosen->save();
        $dataObj = $daychosen;

        return [
            'data' => [
                'dataitem' => $dataObj,
            ]
        ];
    }

    public function addnewfood() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $data = json_decode($_POST["json"]);
        $mymeal = new Meals;
        $mymeal->name = $data->foodname;
        $mymeal->kcals = $data->calories;
        $mymeal->carbs = $data->carbs;
        $mymeal->prot = $data->protein;
        $mymeal->fats = $data->fats;
        $mymeal->user_id = $user->id;
        $mymeal->save();
        $dataObj = $mymeal;

        $todaydate = $_POST["todaydate"];
        $jsDateTS = strtotime($todaydate);
        $todaydate = date('Y-m-d', $jsDateTS );
        $todaydate = $todaydate." 00:00:00";

        $todaydatecheck = Days::where('date',$todaydate)->first();
        if ($todaydatecheck==null) {
            /*$thisday = new Days;
            $thisday->name = $todaydate;
            $thisday->date = $todaydate;
            $thisday->macro_id = $mymacro->id;
            $thisday->type = $data->type;
            $thisday->save();*/
            $meallist="empty adding foods";
        }
        else {
            $day_id = $todaydatecheck->id;

            $dm = new Daymeals;
            $dm->day_id = $day_id;
            $dm->user_id = $user->id;
            $dm->meal_id = $mymeal->id;
            $meallist = [];
            $dm->save();
            $todaysfoods = Daymeals::where('day_id',$day_id)->where('user_id',$user->id)->get();
            foreach ($todaysfoods as $meal) {
                $mealitem = Meals::where('id',$meal->meal_id)->first();
                if ($mealitem!==null) {
                    $mealname = $mealitem->name;
                    $mealid = $mealitem->id;
                    $item = ["name"=>$mealname,"id"=>$mealid];
                    array_push($meallist,$item);
                }
            }
        }
            $allfoods = Meals::where('user_id',$user->id)->get();
            $allmeallist = [];
            foreach ($allfoods as $meal) {
                $item = ["name"=>$meal->name,"id"=>$meal->id];
                array_push($allmeallist,$item);
            }

        //$dataObj = "returninfo";
        return [
            'data' => [
                'dataitem' => $meallist,
                'totalfoods' => $allmeallist
            ]
        ];
    }

    public function addfood() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $data = json_decode($_POST["json"]);
        $foodid = $data->food->id;
        $todaydate = $_POST["todaydate"];
        $jsDateTS = strtotime($todaydate);
        $todaydate = date('Y-m-d', $jsDateTS );
        $todaydate = $todaydate." 00:00:00";
        $mymacro = Macros::where('user_id',$user->id)->where('current',1)->first();
        $thisday = Days::where('macro_id',$mymacro->id)->where('date',$todaydate)->first();
        if ($thisday==null) {
            $thisday = new Days;
            $thisday->name = $todaydate;
            $thisday->date = $todaydate;
            $thisday->macro_id = $mymacro->id;
            $thisday->type = $data->type;
            $thisday->save();
        }

        $realmeal = Meals::where('id',$foodid)->first();
        $daymeal = new Daymeals;
        $daymeal->day_id = $thisday->id;
        $daymeal->meal_id = $foodid;
        $daymeal->user_id =$user->id;
        $daymeal->save();
        $mymeals = Daymeals::where('day_id',$daymeal->day_id)->where('user_id',$user->id)->get();
        $dataObj = [];
        foreach ($mymeals as $meal) {
            $realmeal = Meals::where('id',$meal->meal_id)->first();
            array_push($dataObj,["name"=>$realmeal->name,"id"=>$realmeal->id]);
        }
        return [
            'data' => [
                'dataitem' => $dataObj,
            ]
        ];
    }

    public function changedate() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $data = json_decode($_POST["json"]);

        $todaydate = $data;
        $jsDateTS = strtotime($todaydate);
        $todaydate = date('Y-m-d', $jsDateTS );
        $todaydate = $todaydate." 00:00:00";
        $dataObj = [];
        // get today's foods
        $todaydatecheck = Days::where('date',$todaydate)->first();
        if ($todaydatecheck==null) {
            $myfoods="empty foods";
        }
        else {
        $day_id = $todaydatecheck->id;
        $meallist = [];
        $todaysfoods = Daymeals::where('day_id',$day_id)->where('user_id',$user->id)->get();
        foreach ($todaysfoods as $meal) {
            $mealitem = Meals::where('id',$meal->meal_id)->first();
            if ($mealitem!==null) {
                $mealname = $mealitem->name;
                $mealid = $mealitem->id;
                $item = ["name"=>$mealname,"id"=>$mealid];
                array_push($meallist,$item);
            }
        }
        $myfoods = $meallist;
    }
    $dataObj = $myfoods;
        return [
            'data' => [
                'dataitem' => $dataObj
            ]
        ];
    }

    public function changesummarydate() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $data = json_decode($_POST["json"]);

        $todaydate = $data;
        $jsDateTS = strtotime($todaydate);
        $todaydate = date('Y-m-d', $jsDateTS );
        $todaydate = $todaydate." 00:00:00";
        $mymacro = Macros::where('user_id',$user->id)->where('current',1)->first();
        $mymacro->startdate = $todaydate;
        $mymacro->save();
        $dataObj = $mymacro;
        return [
            'data' => [
                'dataitem' => $data,
            ]
        ];
    }

    public function removefood() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $data = json_decode($_POST["json"]);
        $todaydate = $_POST["todaydate"];
        $jsDateTS = strtotime($todaydate);
        $todaydate = date('Y-m-d', $jsDateTS );
        $todaydate = $todaydate." 00:00:00";
        $mymacro = Macros::where('user_id',$user->id)->where('current',1)->first();
        $mymacro_id = $mymacro->id;
        $day = Days::where('macro_id',$mymacro_id)->where('date',$todaydate)->first();
        $day_id = $day->id;
        $meal_id = $data;
        $delmeal1 = Daymeals::where('day_id',$day_id)->where('meal_id',$meal_id)->where('user_id',$user->id)->first();
        $delmeal1->delete();
        $mymeals = Daymeals::where('day_id',$day_id)->where('user_id',$user->id)->first();
        if ($mymeals!==null) {
            $dataObj = [];
            foreach ($mymeals as $themeal) {
                $dm = Meals::where('id',$themeal->meal_id)->first();
                $dm_name = $dm->name;
                $dm_id = $dm->id;
                array_push($dataObj,["name"=>$dm_name,"id"=>$dm_id]);
            }
        }
        else {
            $delmeal = Days::where('macro_id',$mymacro_id)->where('date',$todaydate)->delete();
            $dataObj="removal";
        }
        return [
            'data' => [
                'dataitem' => $dataObj,
            ]
        ];
    }

    public function fooddiary() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $currentmacro = Macros::where('current',1)->where('user_id',$user->id)->first();

        $startdate = $currentmacro->startdate;
        $mydays = Days::where('macro_id',$currentmacro->id)->get();
        $diary = [];
            foreach ($mydays as $myday) {
                $foodarray = [];
                
                $mymeals = Daymeals::where('day_id',$myday->id)->where('user_id',$user->id)->get();

                foreach ($mymeals as $mymeal) {
                    $meal_id = $mymeal->meal_id;
                    $finalmeals = Meals::where('id',$meal_id)->first();

                    array_push($foodarray,$finalmeals->name);
                }
                array_push($diary,["date"=>$myday->date,"food"=>$foodarray]);
            }
        $dataObj = $diary;
        return [
            'data' => [
                'dataitem' => $dataObj,
            ]
        ];
    }

    public function todaysmacros() {
        $token = (string)JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $data = json_decode($_POST["json"]);
        $todaydate = $_POST["todaydate"];
        $jsDateTS = strtotime($todaydate);
        $todaydate = date('Y-m-d', $jsDateTS );
        $todaydate = $todaydate." 00:00:00";
        $dataObj = [];
        $dataObj["protlimit"]=0;
        $dataObj["kclimit"]=0;
        $dataObj["fatlimit"]=0;
        $dataObj["carblimit"]=0;
        $totalfats = 0;
        $totalcarbs = 0;
        $totalprot = 0;
        $totalkcals = 0;
        if ($todaydate=="") {
            $todaydate = date();
        }
       
                $currentmacro = Macros::where('current',1)->where('user_id',$user->id)->first();
                if ($currentmacro!==null) {
                    $startdate = $currentmacro->startdate;
                    $day = Days::where('date','=',$todaydate)->where('macro_id',$currentmacro->id)->first();
                    if ($day!==null) {
                        // add up totals for todays macros
                        // add up usage for today
                        $day_id = $day->id;
                        $mymeals = Daymeals::where('day_id',$day_id)->get();
                        if ($day->type=="workout") {
                            $dataObj["protlimit"]=$currentmacro->wprot;
                            $dataObj["kclimit"]=$currentmacro->wkcals;
                            $dataObj["fatlimit"]=$currentmacro->wfats;
                            $dataObj["carblimit"]=$currentmacro->wcarbs;
                        }
                        if ($day->type=="rest") {
                            $dataObj["protlimit"]=$currentmacro->prot;
                            $dataObj["kclimit"]=$currentmacro->kcals;
                            $dataObj["fatlimit"]=$currentmacro->fats;
                            $dataObj["carblimit"]=$currentmacro->carbs;

                        }
                 
                        
                        foreach ($mymeals as $mymeal) {
                            $thismeal = Meals::where('id',$mymeal->meal_id)->first();
                            $totalfats = $totalfats + $thismeal->fats;
                            $totalcarbs = $totalcarbs + $thismeal->carbs;
                            $totalprot = $totalprot + $thismeal->prot;
                            $totalkcals = $totalkcals + $thismeal->kcals;
                        }
                    } else {
                        //$dataObj = [];
                    }
                    $dataObj["protsofar"]=$totalprot;
                    $dataObj["kcsofar"]=$totalkcals;
                    $dataObj["fatsofar"]=$totalfats;
                    $dataObj["carbsofar"]=$totalcarbs;
                    $dataObj["thedate"]=$todaydate;
                    $dataObj["extra"]="";
                } else {
                    //$dataObj="nope nothing";
                } 
        return [
            'data' => [
                'dataitem' => $dataObj,
            ]
        ];
    }


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
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
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
