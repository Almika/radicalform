<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Radicalform
 *
 * @copyright   Copyright 2018 Progreccor
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class JFormFieldHistoryradicalform extends JFormField {

	private function getCSV($file, $delimiter = ';')
	{
		$a = [];

		if (file_exists($file) && ($handle = fopen($file, 'r')) !== false)
		{
			while (($data = fgetcsv($handle, 200000, $delimiter)) !== false)
			{
				$a[] = $data;
			}
			fclose($handle);
		}
		return $a;
	}

	function getInput() {


		$params=$this->form->getData()->get("params");

		$log_path = str_replace('\\', '/', JFactory::getConfig()->get('log_path'));

		$data = $this->getCSV($log_path . '/plg_system_radicalform.php', "\t");
		if(count($data)>0)
		{
			for ($i = 0; $i < 6; $i++)
			{
				if (count($data[$i]) < 4 || $data[$i][0][0] == '#')
				{
					unset($data[$i]);
				}
			}
		}
		$data = array_reverse($data);

		$cnt = count($data);

		if ($cnt)
		{
			$html= "<p>".JText::_('PLG_RADICALFORM_HISTORY_SIZE')."<span style='color: green; font-weight: bold'>".filesize($log_path . '/plg_system_radicalform.php')."</span> ".JText::_('PLG_RADICALFORM_HISTORY_BYTE')."</p>";
			$html.="<p><button class='btn btn-danger' id='historyclear'>".JText::_('PLG_RADICALFORM_HISTORY_CLEAR')."</button></p>";
			$html.="<br><br>";
			$html.= '<table class="table table-striped table-bordered adminlist" style="max-width: 900px"><thead><tr>';
			$html.= '<th width="5%">' . JText::_('PLG_RADICALFORM_HISTORY_TIME') . '</th>';
			$html.= '<th width="5%">' . JText::_('PLG_RADICALFORM_HISTORY_DATE') . '</th>';
			$html.= '<th width="5%">' . JText::_('PLG_RADICALFORM_HISTORY_IP') . '</th>';
			$html.= '<th>' . JText::_('PLG_RADICALFORM_HISTORY_MESSAGE') . '</th>';
			$html.= '</tr></thead><tbody>';
			foreach ($data as $i => $item)
			{
				$json = json_decode($item[3],true);
				$json_result = json_last_error() === JSON_ERROR_NONE;

				$itog="";
				if(!$params->hiddeninfo)
				{
					unset($json["reffer"]);
					unset($json["resolution"]);
					unset($json["url"]);
				}
				foreach ($json as $key=>$record) {
					if(is_array($record))
					{
						$record=implode($params->glue, $record);
					}
					$itog.=JText::_($key). ": <b>" . $record ."</b><br />";
				}
				$html.= '<tr class="row' . ($i % 2) . '">' .
					'<td class="nowrap">' . $item[0] . '</td>' .
					'<td>' . $item[1] . '</td>' .
					'<td><a href="http://whois.domaintools.com/'. $item[2] .'" target="_blank">' . $item[2] . '</a></td>';
				if(isset($item[4]) && $item[4]=="WARNING")
				{
					$html .= '<td style="max-width: 700px; overflow: hidden; color: #9f2620;">' . ($json_result ? '' . $itog . '' : htmlspecialchars($item[3])) . '</td>' .
					'</tr>';
				}
				else
				{
					$html .= '<td style="max-width: 700px; overflow: hidden;">' . ($json_result ? '' . $itog . '' : htmlspecialchars($item[3])) . '</td>' .
						'</tr>';
				}

			}
			$html.= '</tbody></table>';
		}
		else
		{
			$html = '<div class="alert">' . JText::_('PLG_RADICALFORM_HISTORY_EMPTY') . '</div>';
		}



		return $html;
	}


}