<meta charset="utf-8">

<?php
header("Content-Type: text/html; charset=UTF-8");
ini_set('display_errors',1);
error_reporting(E_ALL);

?>

<meta charset="utf-8">
<!doctype html>
<html lang="ru">
<style>
c{
font-family: Arial;
  font-size: 20px;
	font-weight: bold;
	color: Yellow;
    text-shadow: 
		-0   -3px 6px #000000,
		 0   -3px 6px #000000,
		-0    3px 6px #000000,
		 0    3px 6px #000000,
		-3px -0   6px #000000,
		 3px -0   6px #000000,
		-3px  0   6px #000000,
		 3px  0   6px #000000,
		-1px -3px 6px #000000,
		 1px -3px 6px #000000,
		-1px  3px 6px #000000,
		 1px  3px 6px #000000,
		-3px -1px 6px #000000,
		 3px -1px 6px #000000,
		-3px  1px 6px #000000,
		 3px  1px 6px #000000,
		-2px -3px 6px #000000,
		 2px -3px 6px #000000,
		-2px  3px 6px #000000,
		 2px  3px 6px #000000,
		-3px -2px 6px #000000,
		 3px -2px 6px #000000,
		-3px  2px 6px #000000,
		 3px  2px 6px #000000,
		-3px -3px 6px #000000,
		 3px -3px 6px #000000,
		-3px  3px 6px #000000,
		 3px  3px 6px #000000,
		-3px -3px 6px #000000,
		 3px -3px 6px #000000,
		-3px  3px 6px #000000,
		 3px  3px 6px #000000;
}
section {

text-align: center;

vertical-align: middle;

}
</style>
<body>
<section>
<c>Либа работает</c>
</section>
</body>
</html>
<?


class tgBot{
    private $token = '';
    public function __construct($token){
        $this->token = $token;
    }
    public function data($par1, $par2 = null, $par3 = null, $par4 = null){
        $data = json_decode(file_get_contents('data.txt'), true);
        if(isset($par4)){
            $var = $data[$par1][$par2][$par3][$par4];
            return $var;
        }
        if(isset($par3)){
            $var = $data[$par1][$par2][$par3];
            return $var;
        }
        if(isset($par2)){
            $var = $data[$par1][$par2];
            return $var;
        }
        else{
            $var = $data[$par1];
            return $var;
        }
    }
    
    public function request($method, $params = []){ //да-да, request на post-е. в коем-то веке.
        $url = 'https://api.telegram.org/bot' . $this->token .  '/' . $method;
        $curl = curl_init();
          
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params); 
          
        $out = json_decode(curl_exec($curl), true);
          
        curl_close($curl); 
          
        return $out; 
    }    
    public function inline_query_send($title, $desc, $message_text, $search) : object 
    {
        $inline_query_id = $this->data( "inline_query", "id");
        //file_put_contents('message.txt', $inline_query_id);
        $count = count($title);
        $results = array();
        for($i=0;$i<$count;$i++) {
        array_push($results, Array("type" => "article", "id" => $title[$i], "title" => $title[$i], "description" => $desc[$i], "input_message_content" => array('parse_mode' => "HTML", 'message_text' => $message_text[$i])));
        }
        if ( $search == 1 ){
            $inline_query_text = $this->data( "inline_query", "query");
            $inline_query_img = str_replace(' ', '%20', $inline_query_text);
            $google = "google.com/search?&q=$inline_query_img";
            $title4 = "поиск в гугл"; // inline title
            $message_text4 = "вот держи\n" . '<a href="' . $google . '">ТЫК</a>'; 
            $url = "https://dummyimage.com/100x100/278/000.png&text=$inline_query_img";
            $url2 = "https://cdn-icons-png.flaticon.com/128/2991/2991148.png";
            array_push($results, Array("type" => "article", "id" => "$title4", "title" => $title4,  "input_message_content" => array('parse_mode' => "HTML", 'message_text' => $message_text4), "parse_mode" => "HTML", "url" => "$google")); 
            
        }
        $results = json_encode($results);
        $a = $this->request( 'answerInlineQuery', [ 'inline_query_id' => $inline_query_id, 'results' => $results, 'cache_time' => 0] );
        return $a;
        exit();
    }
    

    public function alerm_send( int $chat, $testid, $text, $alert) : object
    {
        return $this->request( 'answerCallbackQuery', [ 'callback_query_id' => $testid, 'show_alert' => $alert, 'text' => "$text", 'cache_time' => 0] );
    }
    public function message_delete( int $chat, mixed $sms_id) : object
    {
        return $this->request( 'deleteMessage', ['chat_id' => $chat, 'message_id' => $sms_id] );
    }

    public function message_send($chat,$text){
        $a = $this->request('sendMessage', ["parse_mode" => "markdown", "chat_id" => $chat, "text" => $text]);
        return $a; 
    }
    public function  message_edit($chat,$text, $message_id){
        $a = $this->request('editMessageText', ["parse_mode" => "markdown", "chat_id" => $chat, "text" => $text, "message_id" => $message_id]);
        return $a;
    }
    public function kb_edit(array $kbd = null)
    {
        $chatkb = $this->data( "callback_query", "message", "chat", "id");
        $sms_id_kb = $this->data( "callback_query", "message", "message_id");
        $kbd = is_null( $kbd ) ? $kbd : json_encode( $kbd, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        $a = $this->request('editMessageReplyMarkup', ["chat_id" => $chatkb, "message_id" => $sms_id_kb,  'reply_markup' => $kbd]);
        return $a;
    }
    function kb_create($text, $cb_text, $cb_cb, $cb_next = null){
        if(isset($cb_next)){
            $kb =  ['text' => $text, 'callback_data' => json_encode(array('text' => "$cb_text", 'callback' => "$cb_cb", 'next' => "$cb_next"), JSON_UNESCAPED_UNICODE)];
            return $kb;
        }
        else{
            $kb =  ['text' => $text,  'callback_data' => json_encode(array('text' => "$cb_text", 'callback' => "$cb_cb"), JSON_UNESCAPED_UNICODE)];
            return $kb;
        }
    }
    function kb_send($text, $kb = null){
        if ($text == "1"){
            $chatkb = $this->data( "callback_query", "message", "chat", "id");
            $sms_id_kb = $this->data( "callback_query", "message", "message_id");
            $kb = array("inline_keyboard" => $kb);
            $kb = is_null( $kb ) ? $kb : json_encode( $kb, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
            $a = $this->request('editMessageReplyMarkup', ["chat_id" => $chatkb, "message_id" => $sms_id_kb,  'reply_markup' => $kb]);
            return $a;
        }
        else {
            $chat = $this->data( "message", "chat", "id");
            if(isset($chat)){
                $chat = $this->data( "message", "chat", "id");
            }
            else{
                $chat = $this->data( "callback_query", "message", "chat", "id");
            }
        $kb = array("inline_keyboard" => $kb);
        $kb = is_null( $kb ) ? $kb : json_encode( $kb, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        return $this->request( 'sendMessage', [ 'parse_mode' => 'markdown', 'chat_id' => $chat, 'text' => $text, 'reply_markup' => $kb] );
            }
        
    }
 
    function testbutton(){
            $kb = [        "text" => "Открыть сайт",        "url" => "https://t.me/iv?url=https://example.com"    ];
            $kb = [[$kb]];
            $chat = $this->data( "message", "chat", "id");
            if(isset($chat)){
                $chat = $this->data( "message", "chat", "id");
            }
            else{
                $chat = $this->data( "callback_query", "message", "chat", "id");
            }
        $kb = array("inline_keyboard" => $kb);
        $kb = is_null( $kb ) ? $kb : json_encode( $kb, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        return $this->request( 'sendMessage', [ 'parse_mode' => 'HTML', 'chat_id' => $chat, 'text' => "site", 'reply_markup' => $kb] );
    }

    function get_graf_user($user_id, $chat){
        $grafic = json_decode(file_get_contents('grafic.txt'), true)[0];
        $minday  = date('d', strtotime('Monday  this week'));
        $maxday  = date('d', strtotime('Sunday this week'));
        $this->request('sendMessage', ["parse_mode" => "markdown", "chat_id" => $chat, "text" => "(неделя $minday - $maxday)\nпн - $grafic[пн]\nвт-$grafic[вт]\nср-$grafic[ср]\nчт-$grafic[чт]\nпт-$grafic[пт]\nсб-$grafic[сб]\nвск-$grafic[вск]"]);
    }


}



?>