<?php
namespace Core\Entities;


class Paging extends BaseEntity{
	protected $filter;
    protected $filter2;
    protected $pagesize = 100;//required
    protected $page = 1;
    protected $sort = array();
    protected $start;//required
    protected $end;
    protected $validCols = array();
    protected $newsorts2;
    protected $customData = [];
    
	public function __construct(){
		parent::__construct();
	}
	/*
    public function bindRequest(Request $request){
        if($request->query->has('page')){
            $currentPage = $request->query->getInt('page',1);
            $this->setPage($currentPage);
        }
        if($request->query->has('page-size')){
            $pageSize = $request->query->getInt('page-size',100);
            $this->setPageSize($pageSize);
        }
        if($request->query->has('sort-col')){
            $sortcol = $request->query->get('sort-col');
            $dir = 'asc';
            if($request->query->has('sort-dir')){
                $dir = $request->query->get('sort-dir');
            }
            $this->sort[$sortcol] = $dir;
        }
        if($request->query->has('filter')){
            $this->filter = $request->query->get('filter');
        }

    }*/

	public function setPage($p){
        $this->page = intval($p);
        $this->calculate();
    }
    private function calculate(){
        //calculate offset
        if($this->page > 1) {
            $this->start = ($this->page-1)*$this->pagesize;
        }else{
            $this->start = 0;
        }
        $this->end = $this->start + $this->pagesize;
    }
    public function setPageSize($n){
	    $this->pagesize = intval($n);
	    if($this->pagesize===0){
	        $this->pagesize = 100;
	    }
	    $this->calculate();
    }
    public function setPageSizeToMax(){
        $this->setPageSize(1000000);
    }
    public function getStart(){
	    return $this->start;
    }
    public function getPageSize(){
        return $this->pagesize;
    }
    public function getCurrentPage(){
        return $this->page;
    }
    /**
     * to sort the data
    */
    public function setValidColumns($cols){
        $this->validCols = $cols;

    }
    public function validate(){
        if(count($this->validCols)>0 && count($this->sort)>0){
            $keys = array_keys($this->sort);
            $newsorts = [];
            $newsorts2 = [];
            foreach ($this->validCols as $v){
                $val = str_replace('"',"",$v);
                foreach ($keys as $k){
                    if($k==$val){
                    	//echo $this->sort[$k];
                    	
				        $order = $this->sort[$k];
				        if(!in_array($order,['asc','desc'])){
				        	$order = 'asc';
				        }
                        $newsorts[$v] = $order;
                        $newsorts2[] = ['sortcol'=>$k, 'sortdir'=>$order];
                        break;
                    }
                }
            }
            $this->sort = $newsorts;
            $this->newsorts2 = $newsorts2;
        }
        return !$this->has_error();
    }
    public function setSort($col, $dir){
        $this->sort[$col] = $dir;
    }
    public function getSort(){
        return $this->sort;
    }
    public function getSortArray(){
        return $this->newsorts2;
    }
    public function getFilter(){
        return $this->filter;
    }
    public function setCustomData($key,$val){
        $this->customData[$key]=$val;
    }
    public function getCustomData($key=NULL){
        if($key==NULL){
            return $this->customData;    
        }
        if(array_key_exists($key, $this->customData)){
            $t =  $this->customData[$key];
            if($t!='null'){
                return $t;
            }    
            
        }
        return NULL;
    }
}

