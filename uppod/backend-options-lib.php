<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Plugin «Uppod-плеер» for MaxSite CMS
 * 
 * Author: (c) Илья Земсков (ака Профессор)
 * Plugin URL: http://vizr.ru/page/plugin-uppod-player
 */

	echo mso_load_style( getinfo('plugins_url').basename(dirname(__FILE__)).'/backend-options.css' );
 	echo mso_load_jquery( 'jquery.cookie.js' );
	echo mso_load_jquery( 'jquery.showhide.js' );
	
	echo '
<script>
$(function () {
	$.cookie.json = true; 
	$(".admin_plugin_options_info").showHide({time: 400, useID: false, clickElem: ".link", foldElem: ".admin_plugin_options_group", visible: false});
});
</script>
';

# Переделка стандартной функции mso_admin_plugin_options © Max
function mso_admin_plugin_options2($key, $type, $ar, $title = '', $info = '', $text_other = '', $show_goto_plugins = true)
{
	if( $show_goto_plugins )
	{
		echo '<p><a href="'.getinfo('site_admin_url').'plugins" class="i plugins">'.t('Плагины').'</a></p>';
	}
		
	if( $title )
	{
		echo '<h1><a href="">'.$title.'</a></h1>';
	}
	else
	{
		echo '<h1><a href="">'.t('Опции плагина').'</a></h1>';
	}
		
	if( $info )
	{
		echo '<p class="info">'.$info.'</p>';
	}
	else
	{
		echo '<p class="info">'.t('Укажите необходимые опции плагина.').'</p>';
	}
		
	if( $text_other )
	{
		echo '<p>'.$text_other.'</p>';
	}
		
	# тут получаем текущие опции
	$options = mso_get_option( $key, $type, array() ); # получаем опции
		
	# здесь смотрим post  - в post должен быть $key.'-'.$type
	if( $post = mso_check_post(array('f_session_id', 'f_submit', $key.'-'.$type)) )
	{
		mso_checkreferer(); # защита рефера
			
		# наши опции
		$in = $post[$key.'-'.$type];
			
		if( isset($in['_mso_checkboxs']) ) # есть чекбоксы
		{
			$ch_names = array_keys($in['_mso_checkboxs']); # получили все чекбоксы
			$t = array(); # временный массив
			foreach( $ch_names as $val ) # проверим каждый чекбокс
			{
				if( isset($in[$val]) ) $t[$val] = '1'; # если есть, значит отмечен
			}
				
			$t = array_merge( $in['_mso_checkboxs'], $t ); # объединим с чекбоксамии
			unset($in['_mso_checkboxs']); # удалим _mso_checkboxs
			$in = array_merge( $in, $t ); # объединим с $in
			# теперь в $in все чекбоксы
		}
			
		# перед проверкой удалим из $ar все типы info
		$ar1 = $ar;
		foreach($ar1 as $m => $val)
		{
			if( $val['type'] == 'info' ) unset($ar1[$m]);
		}
			
		# проверяем их с входящим $ar - ключи должны совпадать
		# финт ушами: смотрим разность ключей массивов - красиво?
		# если будет разность, значит неверные входящие данные, все рубим
		if (array_diff(array_keys($ar1), array_keys($in))) die('Error key. :-(');
			
		$newoptions = array_merge($options, $in); # объединим
			
		if( $options != $newoptions )
		{
			mso_add_option($key, $newoptions, $type); # обновим
			$options = $newoptions; # сразу обновим переменную на новые опции
			mso_flush_cache(); # сбросим кэш
		}
			
		echo '<div class="update">'.t('Обновлено!').'</div>';
	}
		
	if( $ar ) # есть опции
	{
		$form = ''; # тут генерируем форму
		$groupnow = false; $k = 0;
		$lim = count($ar); # количество ключей в массиве опций - чтобы знать когда закрывать div
			
		foreach( $ar as $m => $val )
		{
			$k++;
				
			if( $val['type'] == 'info' ) # тип info - группа опций
			{
				if( $groupnow )
				{
					$form .= '</div></div>';
					$groupnow = !$groupnow;
				}
					
				if( isset($val['id']) )
					$tag_id = ' id="'.$val['id'].'"';
				else
					$tag_id= '';
				
				if( isset($val['class']) )
					$tag_class = ' '.$val['class'];
				else
					$tag_class= '';
				
				$form .= '<div class="admin_plugin_options_info'.$tag_class.'"'.$tag_id.'>';
				
				if( isset($val['title']) ) # заголовок группы опций
					$form .= '<h3><a class="link hidden" href="#">'.$val['title'].'</a></h3>';
					
				if( isset($val['text']) )
					$form .= '<p>'.$val['text'].'</p>';
				
				$form .= '<div id="'.$m.'" class="admin_plugin_options_group">';
				
				$groupnow = !$groupnow;
					
				continue;
			}
			
			if( !isset($options[$m]) ) $options[$m] = $val['default'];
			
			$group_start = ( isset($val['group_start']) ) ? $val['group_start'] : '';
			$group_end = ( isset($val['group_end']) ) ? $val['group_end'] : '';
		
			if( $val['description'] ) $val['description'] = '<p class="nop '.$m.'"><div class="fhint">'.$val['description'].'</div></p>';
			
			if( $val['type'] == 'text' )
			{
				if( isset($val['itype']) )
					$itype = $val['itype'];
				else
					$itype = 'text';
				
				if( $itype == 'hidden' )
				{
					$form .= $group_start.'<p class="'.$m.'"><b>' 
							. $val['name'].'</b>'
							. '<input type="'.$itype.'" value="'
							. htmlspecialchars($options[$m]) 
							. '" name="'
							. $key.'-'.$type.'['.$m.']'
							. '"></p>' 
							. $val['description']
							. $group_end.NR;
				}
				else
				{
					$form .= $group_start.'<p class="'.$m.'"><label><b>' 
							. $val['name'].'</b>'
							. '<br><input type="'.$itype.'" value="'
							. htmlspecialchars($options[$m]) 
							. '" name="'
							. $key.'-'.$type.'['.$m.']'
							. '"></label></p>' 
							. $val['description']
							. $group_end.NR;
				}
			}
			elseif( $val['type'] == 'textarea' )
			{
				if( isset($val['rows']) )
					$rows = (int) $val['rows'];
				else
					$rows = 10;
				
				$form .= $group_start.'<p class="'.$m.'"><label><b>' 
						. t($val['name']).'</b>'
						. '<br><textarea rows="'.$rows.'" name="'
						. $key.'-'.$type.'['.$m.']'
						. '">'
						. htmlspecialchars($options[$m]) 
						. '</textarea></label></p>' 
						. $val['description'] 
						. $group_end.NR;
			}
			elseif( $val['type'] == 'checkbox' )
			{
				$ch_val = $options[$m];
					
				if( $ch_val )
					$checked = 'checked="checked"';
				else
					$checked = '';
				
				$form .= $group_start  
						. '<p class="'.$m.'"><label><input class="checkbox" type="checkbox" value="'.$ch_val.'"'
						. ' name="'.$key.'-'.$type.'['.$m.']'.'" '.$checked.'> <b>'
						. $val['name']
						. '</b></label></p>' 
						. $val['description'] 
						. $group_end.NR;
				
				# поскольку не отмеченные чекбоксы не передаются в POST, сделаем массив чекбоксов в hidden
				$form .= '<input type="hidden" name="'.$key.'-'.$type.'[_mso_checkboxs]['.$m.']" value="0">';	
						
			}
			elseif( $val['type'] == 'select' )
			{
				$form .= $group_start.'<p class="'.$m.'"><label><b>' 
						. $val['name'].'</b>'
						. '<br><select name="'
						. $key.'-'.$type.'['.$m.']'
						. '">';
				
				# если есть values, то выводим - правила задания, как в ini-файлах
				if( isset($val['values']) )
				{
					$values = explode('#', $val['values']);
					foreach( $values as $v ) 
					{
						$v = trim($v);
						$v_t = $v;
						
						$ar = explode('||', $v);
						if( isset($ar[0]) ) $v = trim($ar[0]);
						if( isset($ar[1]) ) $v_t = trim($ar[1]);
						
						if( htmlspecialchars($options[$m]) == $v )
							$checked = 'selected="selected"';
						else
							$checked = '';
						$form .= NR.'<option value="'.$v.'" '.$checked.'>'.$v_t.'</option>';
					}
				}
				$form .= '</select></label></p>' 
						. $val['description']
						. $group_end.NR;
			}
			elseif( $val['type'] == 'radio' )
			{
				$form .= $group_start.'<p class="'.$m.'"><b>' 
						. $val['name']
						. '</b></p><p class="nop">';
						
				if( !isset($val['delimer']) )
					$delimer = '<br>';
				else
					$delimer = stripslashes($val['delimer']);
							
				# если есть values, то выводим - правила задания, как в ini-файлах
				if( isset($val['values']) )
				{
					$values = explode('#', $val['values']);
					foreach( $values as $v )
					{
						$v = trim($v);
						$v_t = $v;
							
						$ar = explode('||', $v);
						if (isset($ar[0])) $v = trim($ar[0]);
						if (isset($ar[1])) $v_t = trim($ar[1]);
							
						if( htmlspecialchars($options[$m]) == $v )
							$checked = 'checked="checked"';
						else
							$checked = '';
						
						$form .= NR.'<label class="nocell"><input type="radio" value="'.$v.'" '.$checked.' name="'.$key.'-'.$type.'['.$m.']'.'"> '.$v_t.'</label>'.$delimer;
					}
				}
				
				$form .= '</p>'.$val['description'].$group_end.NR;
			}
				
			if( $k == $lim )
			{
				$form .= '</div></div>';
				$groupnow = !$groupnow;
			}
		}
		
		# выводим форму
		echo NR.'<form method="post" class="fform admin_plugin_options">'.mso_form_session('f_session_id');
		echo $form;
		echo NR.'<button type="submit" name="f_submit" class="i save">'.t('Сохранить').'</button>';
		echo '</form>'.NR;
	}
	else
	{
		echo t('<p>Опции не определены.</p>').NR;
	}
}
?>