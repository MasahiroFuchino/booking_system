<?php
session_start();
require_once('login_function.php');
//GoogleカレンダーAPIから祝日を取得
require_once('shop_header.php');


$year = date("Y");

function getHolidays($year) {//その年の祝日を全て取得する関数を作成

	$api_key = 'AIzaSyAyXH1klEML8b6jH8Spkdc82LQuT5US0lo'; //取得したAPIを入れる
	$holidays = array(); //祝日を入れる配列の箱を用意しておく
	$holidays_id = 'japanese__ja@holiday.calendar.google.com';
	$url = sprintf(
        //sprintf関数を使用しURLを設定
        //このURLはGoogleカレンダー独自のURL
        //Googleカレンダーから祝日を調べるURL
        'https://www.googleapis.com/calendar/v3/calendars/%s/events?'.
		'key=%s&timeMin=%s&timeMax=%s&maxResults=%d&orderBy=startTime&singleEvents=true',
		$holidays_id,
		$api_key,
		$year.'-01-01T00:00:00Z' , // 取得開始日
		$year.'-12-31T00:00:00Z' , // 取得終了日
		150 // 最大取得数
	);

	if ( $results = file_get_contents($url, true )) {
        //file_get_contents関数を使用
        //URLの中に情報が入っていれば（trueなら）以下を実行する
		$results = json_decode($results);
        //JSON形式で取得した情報を配列に格納
		foreach ($results->items as $item ) {
			$date = strtotime((string) $item->start->date);
			$title = (string) $item->summary;
			$holidays[date('Y-m-d', $date)] = $title;
            //年月日をキー、祝日名を配列に格納
		}
		ksort($holidays);
        //祝日の配列を並び替え
        //ksort関数で配列をキーで逆順に（１月からの順番にした）
	}
	return $holidays;
}


$Holidays_array = getHolidays($year);
//getHolidays関数を$Holidays_arrayに代入して使用しやすいようにしておく


//その日の祝日名を取得
function display_to_Holidays($date,$Holidays_array) {
    //※引数1は日付"Y-m-d"型、引数に2は祝日の配列データ
    //display_to_Holidays("Y-m-d","Y-m-d") →引数1の日付と引数2の日付が一致すればその日の祝日名を取得する

	if(array_key_exists($date,$Holidays_array)){
        //array_key_exists関数を使用
        //$dateが$Holidays_arrayに存在するか確認
        //各日付と祝日の配列データを照らし合わせる

		$holidays = "<br/>".$Holidays_array[$date];
        //祝日が見つかれば$holidaysに入れておく
		return $holidays;
	}
}



//
// $pdo = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());
//
// $sql="SELECT open_day,open_flame FROM booking_master";
//
// $res = $pdo->query($sql);
//
// $data = $res->fetchAll();
//
// print_r($data);

// $open_flame = array_column($data, $data['open_flame'],$data['open_day']);
// var_dump($open_flame);
function get_booking($date){

$pdo = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());
$sql_1 = "SELECT * FROM booking WHERE shop_id = ?;";
$stmt_1 = $pdo->prepare($sql_1);
$stmt_1->bindValue(1, $_SESSION['shop_id'], PDO::PARAM_STR);
$stmt_1->execute();
$data_1 = $stmt_1->fetchAll(PDO::FETCH_ASSOC);

$reservation_member_1 = array();


foreach($data_1 as $out_1){
		if ($out_1['open_day']==$date) {
			$count_1[]=$out_1['booking_rest'];
			$member_out_1 = array_sum($count_1);
			// var_dump($member_out);
			$day_out_1 = strtotime((string) $out_1['open_day']);
			//
	    // $member_out = $out['open_flame'];
			//
	    $reservation_member_1[date('Y-m-d', $day_out_1)] = $member_out_1;
			// var_dump($reservation_member_1);

		}
}

		// var_dump($member_out);

    ksort($reservation_member_1);

		return $reservation_member_1;

}
function get_booking_master($date){

$pdo = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());
$sql_2 = "SELECT * FROM booking_master WHERE shop_id = ?;";
$stmt_2 = $pdo->prepare($sql_2);
$stmt_2->bindValue(1, $_SESSION['shop_id'], PDO::PARAM_STR);
$stmt_2->execute();
$data_2 = $stmt_2->fetchAll(PDO::FETCH_ASSOC);

$reservation_member_2 = array();


foreach($data_2 as $out_2){
		if ($out_2['open_day']==$date) {
			$count_2[]=$out_2['open_flame'];
			$member_out_2 = array_sum($count_2);
			// var_dump($member_out);
			$day_out_2 = strtotime((string) $out_2['open_day']);
			//
	    // $member_out = $out['open_flame'];
			//
	    $reservation_member_2[date('Y-m-d', $day_out_2)] = $member_out_2;
		}
}

    ksort($reservation_member_2);
    return $reservation_member_2;

}


// $reservation_array = getreservation();

//getreservation関数を$reservation_arrayに代入しておく
function reservation($date,$booking_array,$booking_master_array){
    //カレンダーの日付と予約された日付を照合する関数

    if(array_key_exists($date,$booking_array)&&array_key_exists($date,$booking_master_array)){
        //もし"カレンダーの日付"と"予約された日"が一致すれば以下を実行する

        if($booking_array[$date] == 0){
            //予約枠人数が0人以下の場合は以下を実行する

        $result = "<br/>"."<span class='green'>"."✕"."</span>";
        $result .= "<br/>"."<span class='green'>".$booking_master_array[$date]-$booking_array[$date]."/".$booking_master_array[$date]."</span>";
				$result .= "<br/>"."<form method='POST' name='a_form' action='42.booking_day_shop.php'>";
				$result .=	"<input type = 'hidden' name = 'open_day' value = '$date' class='green'>";
				$result .=	"<br/>"."<input type = 'submit' name = 'submit' value = '予約詳細確認' class='green'>"."</form>";
        return $result;

    }

        elseif($booking_array[$date] < 10){
            //予約人数が１０人より少なければ以下を実行する

				$result = "<br/>"."<span class='green'>"."△"."</span>";
				$result .= "<br/>"."<span class='green'>".$booking_master_array[$date]-$booking_array[$date]."/".$booking_master_array[$date]."</span>";
				$result .= "<br/>"."<form method='POST' name='a_form' action='42.booking_day_shop.php'>";
				$result .=	"<input type = 'hidden' name = 'open_day' value = '$date' class='green'>";
				$result .=	"<br/>"."<input type = 'submit' name = 'submit' value = '予約詳細確認' class='green'>"."</form>";
				return $result;
        }
        elseif($booking_array[$date] < 20){
            //予約人数が１０人より少なければ以下を実行する
				$result = "<br/>"."<span class='green'>"."〇"."</span>";
				$result .= "<br/>"."<span class='green'>".$booking_master_array[$date]-$booking_array[$date]."/".$booking_master_array[$date]."</span>";
				$result .= "<br/>"."<form method='POST' name='a_form' action='42.booking_day_shop.php'>";
				$result .=	"<input type = 'hidden' name = 'open_day' value = '$date' class='green'>";
				$result .=	"<br/>"."<input type = 'submit' name = 'submit' value = '予約詳細確認' class='green'>"."</form>";
				return $result;


        }
        elseif($booking_array[$date] >= 30){
            //予約人数が１０人より少なければ以下を実行する

				$result = "<br/>"."<span class='green'>"."◎"."</span>";
				$result .= "<br/>"."<span class='green'>".$booking_master_array[$date]-$booking_array[$date]."/".$booking_master_array[$date]."</span>";
				$result .= "<br/>"."<form method='POST' name='a_form' action='42.booking_day_shop.php'>";
				$result .=	"<input type = 'hidden' name = 'open_day' value = '$date' class='green'>";
				$result .=	"<br/>"."<input type = 'submit' name = 'submit' value = '予約詳細確認' class='green'>"."</form>";
				return $result;

        }
    }
}

//タイムゾーンを設定
date_default_timezone_set('Asia/Tokyo');


//前月・次月リンクが選択された場合は、GETパラメーターから年月を取得
if(isset($_GET['ym'])){
$ym = $_GET['ym'];
}else{
//今月の年月を表示
$ym = date('Y-m');
}

//タイムスタンプ（どの時刻を基準にするか）を作成し、フォーマットをチェックする
//strtotime('Y-m-01')
$timestamp = strtotime($ym . '-01');
if($timestamp === false){//エラー対策として形式チェックを追加
//falseが返ってきた時は、現在の年月・タイムスタンプを取得
$ym = date('Y-m');
$timestamp = strtotime($ym . '-01');
}

//今月の日付　フォーマット　例）2020-10-2
$today = date('Y-m-j');
//カレンダーのタイトルを作成　例）2020年10月
$html_title = date('Y年n月', $timestamp);//date(表示する内容,基準)

//前月・次月の年月を取得
//strtotime(,基準)
$prev = date('Y-m', strtotime('-1 month', $timestamp));
$next = date('Y-m', strtotime('+1 month', $timestamp));


//該当月の日数を取得
$day_count = date('t', $timestamp);

//１日が何曜日か
$youbi = date('w', $timestamp);

//カレンダー作成の準備
$weeks = [];
$week = '';

//第１週目：空のセルを追加
//str_repeat(文字列, 反復回数)
$week .= str_repeat('<td></td>', $youbi);

for($day = 1; $day <= $day_count; $day++, $youbi++){

$date = $ym . '-' . $day;
//それぞれの日付をY-m-d形式で表示例：2020-01-23
//$dayはfor関数のおかげで１日づつ増えていく
$Holidays_day = display_to_Holidays(date("Y-m-d",strtotime($date)),$Holidays_array);
//display_to_Holidays($date,$Holidays_array)の$dateに1/1~12/31の日付を入れる

$booking_array = get_booking(date("Y-m-d",strtotime($date)));
$booking_master_array	=	get_booking_master(date("Y-m-d",strtotime($date)));
$reservation = reservation(date("Y-m-d",strtotime($date)),$booking_array,$booking_master_array);

if($today == $date && display_to_Holidays(date("Y-m-d",strtotime($date)),$Holidays_array) && reservation(date("Y-m-d",strtotime($date)),$booking_array,$booking_master_array)){
    //もしその日が今日なら
    $week .= '<td class="today">' . $day . $reservation;//今日の場合はclassにtodayをつける
}elseif(display_to_Holidays(date("Y-m-d",strtotime($date)),$Holidays_array) && reservation(date("Y-m-d",strtotime($date)),$booking_array,$booking_master_array)){
    //もしその日に祝日が存在していたら
    //その日が祝日の場合は祝日名を追加しclassにholidayを追加する
    $week .= '<td class="holiday">' . $day. $reservation ;
}elseif(reservation(date("Y-m-d",strtotime($date)),$booking_array,$booking_master_array)){
    $week .= '<td>' . $day . $reservation;
}else{
    //上２つ以外なら
    $week .= '<td>' . $day;
}
$week .= '</td>';

if($youbi % 7 == 6 || $day == $day_count){//週終わり、月終わりの場合
    //%は余りを求める、||はまたは
    //土曜日を取得

    if($day == $day_count){//月の最終日、空セルを追加
        $week .= str_repeat('<td></td>', 6 - ($youbi % 7));
    }

    $weeks[] = '<tr>' . $week . '</tr>'; //weeks配列にtrと$weekを追加

    $week = '';//weekをリセット
}
}

?>
<html>
<head>
<meta charset='utf-8'>
<link rel="stylesheet" href="css/bootstrap.css">
<title>ユーザーページ予約管理画面</title>
<style>
.container {
 font-family: 'Noto Sans', sans-serif;
}
 h3 {
     margin-bottom: 30px;
 }
 th {
     height: 30px;
     text-align: center;
 }
 td {
     height: 100px;
 }
 .today {
     background: orange;
 }
 th:nth-of-type(1), td:nth-of-type(1) {
     color: red;
 }
 th:nth-of-type(7), td:nth-of-type(7) {
     color: blue;
 }
 .holiday{
     color: red;
 }
</style>

</head>
<body>
  <h3 class="text-center">当月予約状況確認画面</h3>
  <div class="text-center d-flex justify-content-around">
     <h3 class="mb-5 text-center"><a href="?ym=<?php echo $prev; ?>">&lt;</a> <?php echo $html_title; ?> <a href="?ym=<?php echo $next; ?>">&gt;</a></h3>
  </div>
  <table class="text-center table table-bordered">
        <tr>
            <th>日</th>
            <th>月</th>
            <th>火</th>
            <th>水</th>
            <th>木</th>
            <th>金</th>
            <th>土</th>
        </tr>
        <?php
            foreach ($weeks as $week) {
                echo $week;
            }
        ?>
    </table>

</div>

</div>
 </div>


</body>
</html>
