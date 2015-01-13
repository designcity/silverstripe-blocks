<?php

class GridFieldConfig_BlockManager extends GridFieldConfig{

	public $blockManager;

	public function __construct($canAdd = true, $canEdit = true, $canDelete = true, $editableRows = false) {
		parent::__construct();

		$this->blockManager = Injector::inst()->get('BlockManager');
		
		// Get available Areas (for page) or all in case of ModelAdmin
		if(Controller::curr()->class == 'CMSPageEditController'){
			$currentPage = Controller::curr()->currentPage();
			$areasFieldSource = $this->blockManager->getAreasForPageType($currentPage->ClassName);
		} else {
			$areasFieldSource = $this->blockManager->getAreasForTheme();
		}
		
		// EditableColumns only makes sense on Saveable parenst (eg Page), or inline changes won't be saved
		if($editableRows){
			$this->addComponent($editable = new GridFieldEditableColumns());
			$displayfields = array(
				'singular_name' => array('title' => 'Block Type', 'field' => 'ReadonlyField'),
				'Name'        	=> array('title' => 'Name', 'field' => 'ReadonlyField'),
				'BlockArea'	=> array(	
					'title' => 'Block Area
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
						// the &nbsp;s prevent wrapping of dropdowns
					'callback' => function() use ($areasFieldSource){
							return DropdownField::create('BlockArea', 'Block Area', $areasFieldSource)
								->setHasEmptyDefault(true);
						}
				),
				'Published'		=> array('title' => 'Published<br />(global)', 'field' => 'CheckboxField'),
				'PageListAsString' => array('title' => 'Used on pages', 'field' => 'ReadonlyField'),
			);
			$editable->setDisplayFields($displayfields);
		} else {
			$this->addComponent($dcols = new GridFieldDataColumns());
			// Optional copybutton, sadly works only on BlockAdmin
			if(class_exists('GridFieldCopyButton')){
				//$this->addComponent(new GridFieldCopyButton());
			}
			$displayfields = array(
				'singular_name' => array('title' => 'Block Type', 'field' => 'ReadonlyField'),
				'Name'        	=> array('title' => 'Name', 'field' => 'ReadonlyField'),
				// (Block)Area has moved to many_many_extrafields, so not available on the record
				//'Area'			=> array('title' => 'Block area', 'field' => 'ReadonlyField'),
				'PublishedString' => array('title' => 'Published<br />(global)', 'field' => 'ReadonlyField'),
				'PageListAsString' => array('title' => 'Used on pages', 'field' => 'ReadonlyField'),
			);
			$dcols->setDisplayFields($displayfields);
		}

		$this->addComponent(new GridFieldButtonRow('before'));
		$this->addComponent(new GridFieldToolbarHeader());
		$this->addComponent(new GridFieldDetailForm());
		$this->addComponent($sort = new GridFieldSortableHeader());
		$this->addComponent($filter = new GridFieldFilterHeader());
		

		$filter->setThrowExceptionOnBadDataType(false);
		$sort->setThrowExceptionOnBadDataType(false);

		if($canAdd){
			$multiClass = new GridFieldAddNewMultiClass();
			$classes = ArrayLib::valuekey(ClassInfo::subclassesFor('Block'));
			array_shift($classes);
			foreach ($classes as $k => $v) {
				$classes[$k] = singleton($k)->singular_name();
			}
			$multiClass->setClasses($classes);
			$this->addComponent($multiClass);	
		}
		
		if($canEdit){
			$this->addComponent(new GridFieldEditButton());	
		}

		if($canDelete){
			$this->addComponent(new GridFieldDeleteAction(true));
		}

		return $this;		
		
	}

	public function addExisting(){
		$this->addComponent($add = new GridFieldAddExistingSearchButton());
		$add->setSearchList(Block::get());
		return $this;
	}


	public function addBulkEditing(){
		if(class_exists('GridFieldBulkManager')){
			$this->addComponent(new GridFieldBulkManager());
		}
		return $this;
	}

}
