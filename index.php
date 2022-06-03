<?php
/*
Plugin Name: Hylsay Text Reading Pro
Plugin URI: https://blog.aoaoao.info/post/wordpress-plugin-hylsay-text-reading/
Description: 语音合成本地化，一次合成无限次使用，支持字幕跟随。
Version: 1.0.0
Author: hylsay
Author URI: https://blog.aoaoao.info
*/

function hylsay_text_reading_admin_mycss() {
    echo '<style type="text/css">
    .form-table th {
		font-weight:400;
	}
    </style>';
 }
add_action('admin_head', 'hylsay_text_reading_admin_mycss');

class HylsayTextReadingPlugin {
	private $baiduaudio_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'baiduaudio_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'baiduaudio_page_init' ) );
	}

	public function baiduaudio_plugin_page() {
		add_menu_page(
			'文章阅读插件', // page_title
			'文章阅读插件', // menu_title
			'manage_options', // capability
			'baiduaudio', // menu_slug
			array( $this, 'baiduaudio_create_admin_page' ), // function
			'dashicons-admin-generic', // icon_url
			100 // position
		);
	}

	public function baiduaudio_create_admin_page() {
		$this->baiduaudio_options = get_option( 'baiduaudio_option_name' ); ?>

		<div class="wrap">
			<h2>文章阅读插件设置</h2>
			<p><b>插件介绍：</b></p>
			<p>本插件是基于百度语音合成开发，需要自行申请百度语音合成APIkey，地址：<a href="https://ai.baidu.com/tech/speech/tts" target="_blank">https://ai.baidu.com/tech/speech/tts</a></p>
			<p>问题反馈：<a href="https://blog.aoaoao.info/post/wordpress-plugin-hylsay-text-reading/" target="_blank">https://blog.aoaoao.info/post/wordpress-plugin-hylsay-text-reading/</a></p>
			<p><b>插件设置：</b></p>
			<p>1.初始设置。语速、音调、音量这三项，取值0-15，不填默认为5。</p>
			<p>2.声音类型。如果你购买的是基础音库，就选择基础语音对应的类型；如果是精品音库，就选择精品音库对应的类型。</p>
			
            <?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'baiduaudio_option_group' );
					do_settings_sections( 'baiduaudio-admin' );
					submit_button();
				?>
			</form>
			<hr>
			<p>如果您觉得本插件还不错，并且方便了您，望多多给予支持，开发不易呀，谢谢！</p>
			
			<img src="https://img-blog.csdnimg.cn/36a8f46377ad419887e73ddc921906fc.png" alt="打赏是一种美德" width="450px" >
		</div>
	<?php }

	public function baiduaudio_page_init() {
		register_setting(
			'baiduaudio_option_group', // option_group
			'baiduaudio_option_name', // option_name
			array( $this, 'baiduaudio_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'baiduaudio_setting_section', // id
			'基础设置', // title
			array( $this, 'baiduaudio_section_info' ), // callback
			'baiduaudio-admin' // page
		);
		
		add_settings_field(
			'baidu_appID', // id
			'APP_ID', // title
			array( $this, 'baidu_appID_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baidu_apiKey', // id
			'API_KEY', // title
			array( $this, 'baidu_apiKey_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baidu_secretKey', // id
			'SECRET_KEY', // title
			array( $this, 'baidu_secretKey_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'select_post_page', // id
			'应用范围', // title
			array( $this, 'select_post_page_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baidu_spd', // id
			'语速', // title
			array( $this, 'baidu_spd_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baidu_pit', // id
			'音调', // title
			array( $this, 'baidu_pit_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baidu_vol', // id
			'音量', // title
			array( $this, 'baidu_vol_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'select_shengyin', // id
			'选择声音类型', // title
			array( $this, 'select_shengyin_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'select_open_close_zimu', // id
			'显示字幕', // title
			array( $this, 'select_open_close_zimu_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

	
	}

	public function baiduaudio_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['baidu_appID'] ) ) {
			$sanitary_values['baidu_appID'] = sanitize_text_field( $input['baidu_appID'] );
		}

		if ( isset( $input['baidu_apiKey'] ) ) {
			$sanitary_values['baidu_apiKey'] = sanitize_text_field( $input['baidu_apiKey'] );
		}

		if ( isset( $input['baidu_secretKey'] ) ) {
			$sanitary_values['baidu_secretKey'] = sanitize_text_field( $input['baidu_secretKey'] );
		}

		// 选择页面
		if ( isset( $input['select_post_page'] ) ) {
			$sanitary_values['select_post_page'] = sanitize_text_field( $input['select_post_page'] );
		}

		// 语速
		if ( isset( $input['baidu_spd'] ) ) {
			$sanitary_values['baidu_spd'] = sanitize_text_field( $input['baidu_spd'] );
		}
		// 音调
		if ( isset( $input['baidu_pit'] ) ) {
			$sanitary_values['baidu_pit'] = sanitize_text_field( $input['baidu_pit'] );
		}
		//音量
		if ( isset( $input['baidu_vol'] ) ) {
			$sanitary_values['baidu_vol'] = sanitize_text_field( $input['baidu_vol'] );
		}

		// 选择声音类型
		if ( isset( $input['select_shengyin'] ) ) {
			$sanitary_values['select_shengyin'] = $input['select_shengyin'];
		}

		// 开启，关闭字幕
		if ( isset( $input['select_open_close_zimu'] ) ) {
			$sanitary_values['select_open_close_zimu'] = sanitize_text_field( $input['select_open_close_zimu'] );
		}

		return $sanitary_values;
	}

	public function baiduaudio_section_info() {
		echo '<hr>';
	}

	public function select_post_page_callback() {
		?> <fieldset>
		<?php $checked = ( isset( $this->baiduaudio_options['select_post_page'] ) && $this->baiduaudio_options['select_post_page'] === '0' ) ? 'checked' : '' ; ?>
		<label for="select_post_page-0"><input type="radio" name="baiduaudio_option_name[select_post_page]" id="select_post_page-0" value="0" checked > 文章</label> &nbsp;&nbsp;
		<?php $checked = ( isset( $this->baiduaudio_options['select_post_page'] ) && $this->baiduaudio_options['select_post_page'] === '1' ) ? 'checked' : '' ; ?>
		<label for="select_post_page-1"><input type="radio" name="baiduaudio_option_name[select_post_page]" id="select_post_page-1" value="1" <?php echo $checked; ?>> 页面</label> &nbsp;&nbsp;
		<?php $checked = ( isset( $this->baiduaudio_options['select_post_page'] ) && $this->baiduaudio_options['select_post_page'] === '2' ) ? 'checked' : '' ; ?>
		<label for="select_post_page-2"><input type="radio" name="baiduaudio_option_name[select_post_page]" id="select_post_page-2" value="2" <?php echo $checked; ?>> 文章+页面</label>

		</fieldset> <?php
	}

	public function select_open_close_zimu_callback() {
		?> <fieldset>
		<?php $checked = ( isset( $this->baiduaudio_options['select_open_close_zimu'] ) && $this->baiduaudio_options['select_open_close_zimu'] === '0' ) ? 'checked' : '' ; ?>
		<label for="select_open_close_zimu-0"><input type="radio" name="baiduaudio_option_name[select_open_close_zimu]" id="select_open_close_zimu-0" value="0" checked > 开启</label> &nbsp;&nbsp;
		<?php $checked = ( isset( $this->baiduaudio_options['select_open_close_zimu'] ) && $this->baiduaudio_options['select_open_close_zimu'] === '1' ) ? 'checked' : '' ; ?>
		<label for="select_open_close_zimu-1"><input type="radio" name="baiduaudio_option_name[select_open_close_zimu]" id="select_open_close_zimu-1" value="1" <?php echo $checked; ?>> 关闭</label> &nbsp;&nbsp;
		
		</fieldset> <?php
	}

	public function select_shengyin_callback() {
		?> <fieldset>
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '3' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-2"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-2" value="3" checked > 基础音库-度逍遥</label> &nbsp;&nbsp;
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '0' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-0"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-0" value="0" <?php echo $checked; ?>> 度小美</label> &nbsp;&nbsp;
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '1' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-1"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-1" value="1" <?php echo $checked; ?>> 度小宇</label> &nbsp;&nbsp;
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '4' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-3"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-3" value="4" <?php echo $checked; ?>> 度丫丫</label><br>

		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '5003' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-4"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-4" value="5003" <?php echo $checked; ?>> 精品音库-度逍遥</label> &nbsp;&nbsp;

		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '5118' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-5"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-5" value="5118" <?php echo $checked; ?>> 度小鹿</label> &nbsp;&nbsp;
		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '106' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-6"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-6" value="106" <?php echo $checked; ?>> 度博文</label> &nbsp;&nbsp;
		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '110' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-7"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-7" value="110" <?php echo $checked; ?>> 度小童</label> &nbsp;&nbsp;
		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '111' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-8"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-8" value="111" <?php echo $checked; ?>> 度小萌</label> &nbsp;&nbsp;
		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '103' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-9"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-9" value="103" <?php echo $checked; ?>> 度米朵</label> &nbsp;&nbsp;
		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '5' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-10"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-10" value="5" <?php echo $checked; ?>> 度小娇</label>

		</fieldset> <?php
	}
	public function baidu_appID_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baidu_appID]" id="baidu_appID" value="%s">',
			isset( $this->baiduaudio_options['baidu_appID'] ) ? esc_attr( $this->baiduaudio_options['baidu_appID']) : ''
		);
	}

	public function baidu_apiKey_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baidu_apiKey]" id="baidu_apiKey" value="%s">',
			isset( $this->baiduaudio_options['baidu_apiKey'] ) ? esc_attr( $this->baiduaudio_options['baidu_apiKey']) : ''
		);
	}

	public function baidu_secretKey_callback() {
		printf(
			'<input class="regular-text" type="password" name="baiduaudio_option_name[baidu_secretKey]" id="baidu_secretKey" value="%s">',
			isset( $this->baiduaudio_options['baidu_secretKey'] ) ? esc_attr( $this->baiduaudio_options['baidu_secretKey']) : ''
		);
	}
	//语速
	public function baidu_spd_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baidu_spd]" id="baidu_spd" value="%s" placeholder="取值0-15，不填默认为5，中语速">',
			isset( $this->baiduaudio_options['baidu_spd'] ) ? esc_attr( $this->baiduaudio_options['baidu_spd']) : ''
		);
	}

	//音调
	public function baidu_pit_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baidu_pit]" id="baidu_pit" value="%s" placeholder="取值0-15，不填默认为5，中语调">',
			isset( $this->baiduaudio_options['baidu_pit'] ) ? esc_attr( $this->baiduaudio_options['baidu_pit']) : ''
		);
	}

	//音量
	public function baidu_vol_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baidu_vol]" id="baidu_vol" value="%s" placeholder="取值0-15，不填默认为5，中音量">',
			isset( $this->baiduaudio_options['baidu_vol'] ) ? esc_attr( $this->baiduaudio_options['baidu_vol']) : ''
		);
	}

}

if ( is_admin() )
$baiduaudio = new HylsayTextReadingPlugin();

function add_hylsay_text_reading_js_css() {
	echo '<link rel="stylesheet" href="'.plugin_dir_url(__FILE__ ).'css/baiduAudio.css" type="text/css">';
	echo '<script src="'.plugin_dir_url(__FILE__ ).'js/jquery.min.js"></script>';
}
add_action('wp_enqueue_scripts', 'add_hylsay_text_reading_js_css');

//注册一个路由
add_action('rest_api_init', function () {
	register_rest_route('hylsaytextreading', '/hylsay_text_reading_get_baiduAudio_mp3/', array('methods' => 'post', 'callback' => 'hylsay_text_reading_baidu_get_mp3_json',));
});

function hylsay_text_reading_baidu_get_mp3_json() {

	$baiduaudio_options = get_option('baiduaudio_option_name');
	$siteUrl = $_SERVER['SERVER_NAME'];
	// $homeUrl = home_url();
	$homeUrl = '';
	$appID = $baiduaudio_options['baidu_appID'];
	$apiKey = $baiduaudio_options['baidu_apiKey'];
	$secretKey = $baiduaudio_options['baidu_secretKey'];

	$baidu_spd = $baiduaudio_options['baidu_spd']?:5;
	$baidu_pit = $baiduaudio_options['baidu_pit']?:5;
	$baidu_vol = $baiduaudio_options['baidu_vol']?:5;
	$baidu_per = $baiduaudio_options['select_shengyin'];
	// $postID = get_the_ID();
	$postID = $_POST['postID'];
	$lastPath = $_POST['lastPath'];
	$lastJsonPath = $_POST['lastJsonPath'];
	$userContent = $_POST['userContent'];

	if (is_file($lastPath)) {
		$fp = fopen($lastPath,'r');
		fclose($fp);
		unlink($lastPath);
	}

	if (is_file($lastJsonPath)) {
		$fp_json = fopen($lastJsonPath,'r');
		fclose($fp_json);
		unlink($lastJsonPath);
	}

	$post_url = 'https://aoaoao.info/wp-json/hylsay/textreading';
	$post_data = array(
		'appID' => $appID,
		'apiKey' => $apiKey,
		'secretKey' => $secretKey,
		'siteUrl' => $siteUrl,
		'homeUrl' => $homeUrl,
		'postType' => $postType,
		'postID' => $postID,
		'baidu_spd' => $baidu_spd,
		'baidu_pit' => $baidu_pit,
		'baidu_vol' => $baidu_vol,
		'baidu_per' => $baidu_per,
		'userContent' => $userContent,
	);
	
	$lastUrlPath = send_post($post_url,$post_data);
    $getStr = json_decode($lastUrlPath,true);

	$file_mp3 = file_get_contents($getStr['lastMp3Url']);
	file_put_contents($lastPath, $file_mp3);

	$file_json = file_get_contents($getStr['lastJsonUrl']);	
	file_put_contents($lastJsonPath, $file_json);

	return 'success';
}

function hylsay_text_reading_add_audio($content) {
	$postID = get_the_ID();
	$userContent = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", '', strip_tags(get_the_content()));
	$homeUrl = home_url();
	if (get_post_type(get_the_ID()) == 'post') {
		$publicPath = plugin_dir_path(__FILE__ ).'post/';
		$publicUrlPath = plugin_dir_url(__FILE__ ).'post/';
	} elseif (get_post_type(get_the_ID()) == 'page') {
		$publicPath = plugin_dir_path(__FILE__ ).'page/';
		$publicUrlPath = plugin_dir_url(__FILE__ ).'page/';
	}
	$lastPath = $publicPath.'audio-'.$postID.'.mp3';
	$lastUrlPath = $publicUrlPath.'audio-'.$postID.'.mp3';

	$lastJsonUrl = $publicUrlPath.'audio-'.$postID.'.json';
	$lastJsonPath = $publicPath.'audio-'.$postID.'.json';

	$baiduaudio_options = get_option( 'baiduaudio_option_name' );
	$select_post_page = $baiduaudio_options['select_post_page'];
	$select_open_close_zimu = $baiduaudio_options['select_open_close_zimu'];

	if (!is_file($lastPath) || !is_file($lastJsonPath)) {
		return '<div id="hylsaybaiduAudioWrap"style="text-align: center;height: 40px;line-height:40px;background: #20a0ff;border-radius: 100px;box-shadow: -.1768rem -.1768rem .25rem hsla(0, 0%, 0%, .35), .1326rem .1326rem .3rem rgba(54, 100, 152, .15), inset 0 0 0 transparent, inset max(1px, .125rem) max(1px, .125rem) max(1px, .25rem) hsla(0, 0%, 100%, .6);">语音努力合成中。。。</div>'.$content.'<script>$.ajax({url:"'.$homeUrl.'/wp-json/hylsaytextreading/hylsay_text_reading_get_baiduAudio_mp3/",type:"POST",data:{postID:'.$postID.',userContent:"'.$userContent.'",lastPath:"'.$lastPath.'",lastJsonPath:"'.$lastJsonPath.'"},dataType:"json",success:function(){window.location.reload()}});</script>';

	} else {
		if ($select_open_close_zimu == 0) {
			$return_str = '<div id="hylsaybaiduAudioWrap">[audio src="'.$lastUrlPath.'"][/audio]</div>
				'.hylsay_text_reading_js($lastJsonUrl);
		} else {
			$return_str = '<div id="hylsaybaiduAudioWrap">[audio src="'.$lastUrlPath.'"][/audio]</div>';
		}

		if ($select_post_page == 0) {
			if (is_single()) {
				return $return_str.$content; 
			}
		} elseif ($select_post_page == 1) {
			if (is_page()) {
				return $return_str.$content; 
			}
		} elseif ($select_post_page == 2) {
			if (is_single() || is_page()) {
				return $return_str.$content;  
			}
		}
	}
}

function hylsay_text_reading_js($lastJsonUrl) {
	return  
	'<div id="hylsaybaiduAudioWrap_box">
		<div class="cover"></div>
	</div><script>var url="'.$lastJsonUrl.'";$.getJSON(url,function(data){var audio=document.querySelector("audio");var oLrc={ms:[]};$.each(data,function(i,item){oLrc.ms.push({t:data[i].startTime,c:data[i].returnTxt})});var $ul=$("<ul></ul>");for(var i=0;i<oLrc.ms.length;i++){var $li=$("<li></li>").text(oLrc.ms[i].c);$ul.append($li)}$(".cover").append($ul);var lineNo=0;var preLine=0.5;var lineHeight=-30;function highLight(){var $li=$(".cover ul li");$li.eq(lineNo).addClass("active").siblings().removeClass("active");if(lineNo>preLine){$ul.stop(true,true).animate({top:(lineNo-preLine)*lineHeight})}}highLight();audio.addEventListener("timeupdate",function(){if(lineNo==oLrc.ms.length)return;var curT=audio.currentTime;var x=getLineNo(curT);lineNo=x;highLight();lineNo++});function getLineNo(ct){if(ct>=parseFloat(oLrc.ms[lineNo].t)){for(var i=oLrc.ms.length-1;i>=lineNo;i--){if(ct>=parseFloat(oLrc.ms[i].t)){return i}}}else{for(var i=0;i<=lineNo;i++){if(ct<=parseFloat(oLrc.ms[i].t)){return i-1}}}}function goBack(){lineNo=0;$ul.animate({top:0});highLight()}audio.addEventListener("ended",function(){goBack()})});</script>';
}

function send_post($url, $post_data) {
 
    $postdata = http_build_query($post_data);
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded;charset=UTF-8',
            'content' => $postdata,
            'timeout' => 15 * 60 // 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
}

add_action('save_post',"hylsay_text_reading_save_post_mp3Json", 1); 
add_filter("the_content", "hylsay_text_reading_add_audio");

function hylsay_text_reading_save_post_mp3Json() {
	$postID = get_the_ID();
	$homeUrl = home_url();
	$userContent = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", '', strip_tags(get_the_content()));

	if (get_post_type(get_the_ID()) == 'post') {
		$publicPath = plugin_dir_path(__FILE__ ).'post/';
		$publicUrlPath = plugin_dir_url(__FILE__ ).'post/';
	} elseif (get_post_type(get_the_ID()) == 'page') {
		$publicPath = plugin_dir_path(__FILE__ ).'page/';
		$publicUrlPath = plugin_dir_url(__FILE__ ).'page/';
	}
	$lastPath = $publicPath.'audio-'.$postID.'.mp3';
	$lastJsonPath = $publicPath.'audio-'.$postID.'.json';
	$post_url = ''.$homeUrl.'/wp-json/hylsaytextreading/hylsay_text_reading_get_baiduAudio_mp3/';
	$post_data = array(
		'postID' => $postID,
		'userContent' => $userContent,
		'lastPath' => $lastPath,
		'lastJsonPath' => $lastJsonPath
	);
	send_post($post_url,$post_data);
}
