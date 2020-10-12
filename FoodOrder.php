<?php
require 'FormHelper.php';

//select메뉴 선택항목 배열생성
//이 배열은 display_form(),validate_form(),process_form()에서사용되므로 
//전역영역에 선언한다

$sweets =array('puff'=>'참깨 퍼프',
'square'=> '코코넛 우유 젤리',
'cake' => '흑설탕 케이크',
'ricemeat' => '찹쌀 경단');

$main_dishes = array('cuke' => '데친 해삼',
'stomach'=>'순대',
'tripe' => '와인 소스 양대창',
'taro'=> '돼지고기 토란국',
'giblets' => '곱창 소금 구이',
'abalone' => '전복 호박 볶음');


//메인페이지 로직
//폼이 제출되면 검증 과정을 거쳐 처리하거나 폼을 다시 출려하고
//제출되지 않았으면 폼을 추력

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    //validate_form()이 오류 메시지를 반환하면 show_form()로 전달
    list($errors, $input) = validate_form();
    if($errors){
        show_form($errors);
    }else{
        //제출 데이터가 검증을 통과하면 처리
        process_form($input);
    }
}else{
    //폼이 제출되지 않으면
    show_form();

}

function show_form($errors = array()){
    $defaults =array('delivery' => 'yes',
    'size'=> 'medium');
    $form = new FormHelper($defaults);


    include 'complete-form.php';
}

function validate_form(){
    $input = array();
    $errors = array();

    $input['name']=trim($_POST['name'] ?? '');
    if(!strlen($input['name'])){
        $errors[] = "이름을 입력해주세요.";
    }

    //$size는 필수항목
    $input['size'] = $_POST['size'] ?? '';
    if(!in_array($input['size'], ['samll', 'medium','large'])){
        $errors[]='크기를 선택해주세요.';
    }
    //sweet필수
    $input['sweet'] = $_POST['sweet'] ?? '';
    if(!array_key_exists($input['sweet'], $GLOBALS['sweets'])){
        $errors[] ='디저트를 선택해주세요.';
    }

    $input['main_dish']  = $_POST['main_dish'] ?? array();
    if(count($input['main_dish']) != 2){
        $errors[] = '주 요리를 두가지 선택해주세요.';
    }else{
        //주요리 두가지 선택됐다면
        //두요리가 모두 유효한지 검사
        if(!(array_key_exists($input['main_dish'][0],$GLOBALS['main_dishes'])&& array_key_exists($input['main_dish'][1],$GLOBALS['main_dishes']))){
            $errors[]='주 요리를 두가지 선택해주세요';
        }
    }

    //delivery 가 선택됐으면 comments에 내용이 있어야
    $input['delivery'] = $_POST['delivery'] ?? 'no';
    $input['comments'] = trim($_POST['comments']??'');
    if(($input['delivery']=='yes')&&(!strlen($input['comments']))){
        $errors[] ='배달 주소를 입력해주세요';
    }
    return array($errors, $input);
}

function process_form($input){
    //$GLOBALS['sweet']와 $GLOBALS['main_dishes']배열찾기
    $sweet = $GLOBALS['sweets'][$input['sweet']];
    $main_dish_1 = $GLOBALS['main_dishes'][$input['main_dish'][0]];
    $main_dish_2 = $GLOBALS['main_dishes'][$input['main_dish'][1]];
    if(isset($input['delivery']) && ($input['deliver'] == 'yes')){
        $delivery = '배달';
    }else{
        $delivery = '매장방문';
    }

    //주문 메시지 텍스트 생성
    $message=<<<_ORDER_
주문이 완료되었습니다, {$input['name']}님.
$sweet({$input['size']}), $main_dish_1, $main_dish_2 를 주문하셨습니다.
배달 여부: $delivery
_ORDER_;
        if(strlen(trim($input['comments']))){
$message .='남기신 메모: '.$input['comments'];
        }

        //주방장에게 메시지 보내기
        //mail('shee0201@naver.com', 'New Order', $message);
        //html 엔티티 인코딩 후 메시지를 출력하고
        //줄바꿈음<br/>태그로 변경
        print nl2br(htmlentities($message,ENT_HTML5));
        
}

?>