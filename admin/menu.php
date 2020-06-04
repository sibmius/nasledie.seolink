<?

	\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

	$MODULE_ID   = 'nasledie.seolink';
	$MODULE_CODE = 'nasledie_seolink';

	$moduleSort = 1;
	$i          = 0;
	$MOD_RIGHT  = $APPLICATION->GetGroupRight($MODULE_ID);

	if ($MOD_RIGHT > "D") {
		$aMenu = array(
			"parent_menu" => "global_menu_nasledie",
			"sort"        => $moduleSort,
			"section"     => $MODULE_ID,
			//"url"         => '/bitrix/admin/settings.php?lang=' . LANGUAGE_ID . '&mid=' . $MODULE_ID . '&mid_menu=1',
			"text"        => GetMessage($MODULE_CODE . '_MAIN_MENU_LINK_NAME'),
			"title"       => GetMessage($MODULE_CODE . '_MAIN_MENU_LINK_DESCRIPTION'),
			"icon"        => $MODULE_CODE . '_icon',
			"page_icon"   => $MODULE_CODE . '_page_icon',
			"items_id"    => $MODULE_CODE . '_main_menu_items',
			"items"       => array()
		);


		$arFiles = array(
			'url' => array('edit','del'),
			'group' => array('edit','del'),
			'stats'=>array(),
			'zamena'=>array(),
			'lostkey'=>array(),
			'losturl'=>array(),
		);


		$i++;
		foreach ($arFiles as $fname => $arExtFname) {


			$arTmp = array(
				'url'       => '/bitrix/admin/' . $MODULE_ID . '_' . $fname . '.php?lang=' . LANGUAGE_ID,
				'more_url'  => array(),
				'module_id' => $MODULE_ID,
				'text'      => GetMessage($MODULE_CODE . '_' . $fname . '_MENU_LINK_NAME'),
				"title"     => GetMessage($MODULE_CODE . '_' . $fname . '_MENU_LINK_DESCRIPTION'),
				//"icon"        => $MODULE_CODE.'_'.$item.'_icon', // ����� ������
				// "page_icon"   => $MODULE_CODE.'_'.$item.'_page_icon', // ������� ������
				'sort'      => $moduleSort + $i,
			);

			foreach($arExtFname as $extfname)
			{
				$arTmp['more_url'][] = '/bitrix/admin/' . $MODULE_ID . '_' . $fname . '_' . $extfname . '.php?lang=' . LANGUAGE_ID;
			}

			$aMenu['items'][] = $arTmp;
		}


		$aModuleMenu[] = $aMenu;
		return $aModuleMenu;
	}
	return false;
