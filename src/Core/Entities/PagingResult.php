<?php
namespace Core\Entities;

class PagingResult extends BaseEntity{
	public $totaldisplayrecords = 0;
    public $totalrecords = 0 ;
    public $data = NULL;
    public $start = 0;
    public $end = 0;
    public $page = 1;
    public $totalpages = 0;
    public $sort;
    //move to next
	public function calculate(Paging $paging){
		$this->start = $paging->getStart();
		$this->end = $this->start+$this->totaldisplayrecords;
		$this->page = $paging->getCurrentPage();
		$this->totalpages = ceil($this->totalrecords/$paging->getPageSize());
		$this->sort = $paging->getSortArray();
	}
	public function setTotalRecords($total){
	    $this->totalrecords = $total;
    }
    public function setData($arrData){
	    $this->data = $arrData;
	    $this->totaldisplayrecords = count($arrData);
    }
}
