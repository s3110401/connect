<?
require_once('model_region.php');
require_once('model_grapevariety.php');
require_once('model_wine.php');
require_once('model_winevariety.php');
require_once('model_orders.php');
require_once('model_customer.php');

require_once('helpers.php');

/** Part C */
require_once ("MiniTemplator.class.php");

DEFINE("DEFAULT_ORDER_COLUMN",0);
DEFINE("COLUMN_TOTAL_STOCK_SOLD",9);
DEFINE("COLUMN_TOTAL_SALES_REVENUE",10);


class Controller
{
   /**
    * @var $mini_t  MiniTemplator model.
    */
   private $mini_t;

   /**
    * retrieves the view file, checked by index.php, and use actions
    *
    * @param string $actions     Actions to select which view you want.
    * @param string $file_name   Choose a file from connect directory.
    * @return void
    */
   public function init($actions,$file_name)
   {
      $this->mini_t = new MiniTemplator;
      $this->mini_t->readTemplateFromFile($_SERVER['DOCUMENT_ROOT'] . $_SERVER["ASSIGN_PATH"] .$file_name);
      $this->mini_t->setVariable("ASSIGN_PATH",$_SERVER["ASSIGN_PATH"]);

      $action = '_'.$actions.'Action';
      $this->$action();

      $this->mini_t->generateOutput();
   }

   /**
    * @var $models_[a-z]+  Common models used in the actions.
    */
   private $model_wine;
   private $model_winevariety;
   private $model_region;
   private $model_grape_varity;

   /**
    * Repetitive actions are put in here
    *
    * @return void.
    */
   private function commonActions()
   {
      /** Default sql starting from row in table */
      $this->limit_start = DEFAULT_START_LIMIT;

      /** Create new models class. */
      $this->model_orders = new ModelOrders();
      $this->model_wine = new ModelWine();
      $this->model_winevariety = new ModelWineVariety();
      $this->model_region = new ModelRegion();
      $this->model_grape_varity = new ModelGrapeVariety();

      /**
       * query all results for their respective models.
       * For the select boxes.
       */
      $this->wine_year_results = $this->model_wine->query_years();
      $this->region_results = $this->model_region->query_region();
      $this->grape_variety_results = $this->model_grape_varity->query_grape_variety();

      /** Get $_GET requests and check if they are numbers. */
      $this->wine_year_lo = 0;
      if(isset($_GET['wine_year_lo']) && preg_match("/^[0-9]+$/", $_GET['wine_year_lo']))
      {
         $this->wine_year_lo = $_GET['wine_year_lo'];
      }
      $this->wine_year_hi = 0;
      if(isset($_GET['wine_year_hi']) && preg_match("/^[0-9]+$/", $_GET['wine_year_hi']))
      {
         $this->wine_year_hi = $_GET['wine_year_hi'];
      }
      $this->html_year_error = "";
      if($this->wine_year_lo > $this->wine_year_hi)
      {
         $this->html_year_error = 
            '<span style="color:red;">Low year must be lower than High year.</span>';
      }
      $this->mini_t->setVariable('html_year_error',$this->html_year_error);

      foreach($this->wine_year_results as $row)
      {
         /** 
          * Check if $_GET year request is equal to
          * the select so we can remember the selected box.
          */
         if($this->wine_year_lo == $row["year"])
         {
            $this->mini_t->setVariable(
               "select_year_lo",'<option value="'.$row["year"].'" selected>'.$row["year"].'</a>');
         }
         else
         {
            $this->mini_t->setVariable(
               "select_year_lo",'<option value="'.$row["year"].'">'.$row["year"].'</a>');
         }
         $this->mini_t->addBlock("year_lo_select_block");
      }

      foreach($this->wine_year_results as $row)
      {
         /** 
          * Check if $_GET year request is equal to
          * the select so we can remember the selected box.
          */
         if($this->wine_year_hi == $row["year"])
         {
            $this->mini_t->setVariable(
               "select_year_hi",'<option value="'.$row["year"].'" selected>'.$row["year"].'</a>');
         }
         else
         {
            $this->mini_t->setVariable(
               "select_year_hi",'<option value="'.$row["year"].'">'.$row["year"].'</a>');
         }
         $this->mini_t->addBlock("year_hi_select_block");
      }
      
      /** Get $_GET requests and check if they are numbers. */
      $this->grape_variety = 0;
      if(isset($_GET['grape_variety']) && preg_match("/^[0-9]+$/", $_GET['grape_variety']))
      {
         $this->grape_variety = $_GET['grape_variety'];
      }
      foreach($this->grape_variety_results as $row)
      {
         /** 
          * Check if $_GET grape_variety request is equal to
          * the select so we can remember the selected box.
          */
         if($this->grape_variety == $row["variety_id"])
         {
            $this->mini_t->setVariable(
               "select_grape_variety",'<option value="'.$row["variety_id"].'" selected>'.$row["variety"].'</a>');
         }
         else
         {
            $this->mini_t->setVariable(
               "select_grape_variety",'<option value="'.$row["variety_id"].'">'.$row["variety"].'</a>');
         }
         $this->mini_t->addBlock("grape_variety_select_block");
      }
      
      /** Get $_GET requests and check if they are numbers. */
      $this->min_cost = 0;
      if(isset($_GET['min_cost']))
      {
         $min_cost = preg_replace('/^\$/', '', $_GET["min_cost"]);
         if(preg_match("/^[0-9.]+$/", $min_cost))
         {
            $this->min_cost = $min_cost;
         }
      }
      $this->mini_t->setVariable("min_cost",$this->min_cost);
      
      /** Get $_GET requests and check if they are numbers. */
      $this->max_cost = 0;
      if(isset($_GET['max_cost']))
      {
         $max_cost = preg_replace('/^\$/', '', $_GET["max_cost"]);
         if(preg_match("/^[0-9.]+$/", $max_cost))
         {
            $this->max_cost = $max_cost;
         }
      }
      $this->mini_t->setVariable("max_cost",$this->max_cost);

      /** Show error if min ocst is greater than max cost */
      $this->html_cost_error = "";
      if($this->min_cost > $this->max_cost)
      {
         $this->html_cost_error =
            '<span style="color:red;">Min Cost must be lower than Max Cost</span>';
      }
      $this->mini_t->setVariable("html_cost_error",$this->html_cost_error);

      /** 2 to 9 because region All is 1 and region_id 1 produces no results. */
      $this->region = 0;
      if(isset($_GET['region']) && preg_match("/^[2-9]+$/", $_GET['region']))
      {
         $this->region = $_GET['region'];
      }
      foreach($this->region_results as $row)
      {
         /** 
          * Check if $_GET region request is equal to
          * the select so we can remember the selected box.
          */
         if($this->region == $row["region_id"])
         {
            $this->mini_t->setVariable(
               "select_region",'<option value="'.$row["region_id"].'" selected>'.$row["region_name"].'</a>');
         }
         else
         {
            $this->mini_t->setVariable(
               "select_region",'<option value="'.$row["region_id"].'">'.$row["region_name"].'</a>');
         }
         $this->mini_t->addBlock("region_select_block");
      }

      /** Allow if true to do a search query at results action. */
      $this->allow_search = true;
      $this->winesearch = "";
      if(isset($_GET['winesearch']))
      {
         $this->winesearch = $_GET['winesearch'];
         /** Allows all letters. */
         if(!preg_match("/^[A-Za-z]+$/", $_GET['winesearch']) &&
            $_GET['winesearch'] != "")
         {
            /** Don't allow search query because $_GET request failed. */
            $this->allow_search = false;
         }
      }
      /** Put $_GET winesearch request back into the view. */
      $this->mini_t->setVariable('winesearch',$this->winesearch);

      $this->winerysearch = "";
      if(isset($_GET['winerysearch']))
      {
         $this->winerysearch = $_GET['winerysearch'];

         /** Allows spaces as well as all letters. */
         if(!preg_match("/^[A-Za-z ]+$/", $_GET['winerysearch']) &&
            $_GET['winerysearch'] != "")
         {
            /** Don't allow search query because $_GET request failed. */
            $this->allow_search = false;
         }
      }
      /** Put $_GET winerysearch request back into the view. */
      $this->mini_t->setVariable('winerysearch',$this->winerysearch);

      $this->column = DEFAULT_ORDER_COLUMN;
      /** allow for 2 numbers */
      if(isset($_GET['column']) && preg_match("/^[0-9]{0,2}$/", $_GET['column']))
      {
         $this->column = $_GET['column'];
      }

      /** Add above $_GET requests to string for html link. */
      $this->add_gets = 'region='.$this->region;
      $this->add_gets .= '&amp;wine_year_lo='.$this->wine_year_lo;
      $this->add_gets .= '&amp;wine_year_hi='.$this->wine_year_hi;
      $this->add_gets .= '&amp;grape_variety='.$this->grape_variety;
      $this->add_gets .= '&amp;min_cost='.$this->min_cost;
      $this->add_gets .= '&amp;max_cost='.$this->max_cost;

      /** Replace whitespace with %20 for w3c standards. */
      $this->add_gets .= '&amp;winesearch='.str_replace(' ', '%20', $this->winesearch);
      $this->add_gets .= '&amp;winerysearch='.str_replace(' ', '%20', $this->winerysearch);

      /** Create url for columns. */
      $this->html_column = '?'.$this->add_gets.'&amp;column=';

      /** Add columns to add_gets. */
      $this->add_gets .= '&amp;column='.$this->column;

      /** Format html a href link. */
      $this->html_nxt_link = '<a href="'.$_SERVER["ASSIGN_PATH"].'index.html">reset search</a><br />';
      $this->html_nxt_link .= '<a href="'.$_SERVER["ASSIGN_PATH"].'results.html?'.$this->add_gets.'">reset pagination</a>';
   }

   /**
    * Start Page
    *
    * @return void.
    */
   protected function _indexAction()
   {
      $this->commonActions();
   }

   /**
    * Action to show a paginated list of wines.
    *
    * @return void.
    */
   protected function _resultsAction()
   {
      $this->commonActions();

      /**
       * For pagination. Check if next is less or equals to 0 or
       * check if $_GET next request has failed number conditions
       * and tehn set to default limits.
       */
      if(!isset($_GET['next']) ||
         (isset($_GET['next']) && $_GET['next'] <= 0) ||
         (isset($_GET['next']) && !preg_match("/^[0-9]+$/", $_GET['next'])))
      {
         /**
          * Default sql total number of results per page.
          * TODO: change variable name
          */
         $this->limit_end = DEFAULT_TOTAL_LIMIT;
         
         /** Default html links. */
         $this->prev_link = DEFAULT_START_LIMIT;
         $this->next_link = DEFAULT_END_LIMIT;
      }
      /** otherwise put $_GET next request into sql and html strings. */
      else
      {
         /**
          * Sql total number of results per page.
          */
         $this->limit_end = DEFAULT_TOTAL_LIMIT;
         
         // User input sql starting from row in table.
         $this->limit_start = $_GET['next'];

         // User input html next and previous links
         $this->prev_link = $_GET['next'] - ADD_TO_LIMIT;
         $this->next_link = $_GET['next'] + ADD_TO_LIMIT;
      }
      
      /**
       * Select box selected and now adding to sql query.
       * Where $selectsearch[key] is the column name and
       * the value is the user input select.
       */
      $selectsearch = array();
      if($this->region != 0)
      {
         $table_column = '`winery`.`region_id`';
         $selectsearch[$table_column] = $this->region;
      }
      if($this->grape_variety != 0)
      {
         $table_column = '`wine_variety`.`variety_id`';
         $selectsearch[$table_column] = $this->grape_variety;
      }

      $this->wine_results = array();
      /** Allow if true to do a search query at results action. */
      if($this->allow_search)
      {
         $this->wine_results = 
            /**
             * wine_variety model has the sql with lots ofjoins.
             *
             * $this->winesearch    from $_GET['winesearch'] request.
             * $this->winerysearch  from $_GET['winerysearch'] request.
             * $selectsearch        from select box $_GET requests.
             * $this->wine_year_lo  $_GET['wine_year_lo'] request.
             * $this->wine_year_hi  $_GET['wine_year_hi'] request.
             * $this->min_cost      $_GET['min_cost'] request.
             * $this->max_cost      $_GET['max_cost'] request..
             * $this->limit_start   $_GET['next'] request.
             * $this->limit_end     from DEFAULT_TOTAL_LIMIT which is 30.
             */
            $this->model_winevariety->search_wine_name($this->winesearch,
               $this->winerysearch,
               $selectsearch, 
               $this->column,
               $this->wine_year_lo,
               $this->wine_year_hi,
               $this->min_cost,
               $this->max_cost,
               $this->limit_start,
               $this->limit_end);

         /** 
          * Set wine pagination results here instead of
          * the view script because of miniTemplator.
          */
         foreach($this->wine_results as $row)
         {
            $this->mini_t->setVariable('wine_id', $row['wine_id']);
            $this->mini_t->setVariable('wine_name', $row['wine_name']);
            $this->mini_t->setVariable('variety', $row['variety']);
            $this->mini_t->setVariable('wine_type', $row['wine_type']);
            $this->mini_t->setVariable('winery_name', $row['winery_name']);
            $this->mini_t->setVariable('region_name', $row['region_name']);
            $this->mini_t->setVariable('on_hand', $row['on_hand']);
            $this->mini_t->setVariable('cost', $row['cost']);
            $this->mini_t->setVariable('total_qty', $row['total_qty']);
            $this->mini_t->setVariable('total_price', $row['total_price']);
            $this->mini_t->addBlock("wine_pagination_block");
         }
      }

      /** 
       * Check the wine pagination results if equal
       * to zero and if so add error to miniTemplator 
       * variable.
       */
      if(count($this->wine_results) == 0)
      {
       $this->mini_t->setVariable('no_records',
         '<p style="color:red;">No records match your search criteria</p>');
      }

      /**
       * Add 'Next' link if not at the end of pagination.
       * Assumed that the number of results is equal to
       * the total number of results limit set from 
       * the sql query.
       */
      if(count($this->wine_results) == $this->limit_end)
      {
         $this->html_nxt_link = '<a href="?next='.$this->next_link .'&amp;'.$this->add_gets.'">Next &gt;&gt;</a>';
         $this->mini_t->setVariable('html_nxt_link', $this->html_nxt_link);
      }
      $this->mini_t->setVariable('html_nxt_link', $this->html_nxt_link);

      /** 
       * Set html_column from commonAction here because 
       * it's not needed anywhere else.
       */
      $this->mini_t->setVariable('html_column', $this->html_column);

      /** Add 'Previous' link if not at the beginning of pagination. */
      $this->html_prv_link = '';
      if($this->limit_start != DEFAULT_START_LIMIT)
      {
         $this->html_prv_link = '<a href="?next='.$this->prev_link.'&amp;'.$this->add_gets.'">&lt;&lt; Previous</a>';
         $this->mini_t->setVariable('html_prv_link', $this->html_prv_link);
      }
   }

   /**
    * Action to show information for a
    * particular Wine via wine_id.
    *
    * @return void.
    */
   protected function _wineinfoAction()
   {
      $this->wine_id = 0;
      if(isset($_GET['wine_id']) && preg_match("/^[0-9]+$/", $_GET['wine_id']))
      {
         $this->wine_id = $_GET['wine_id'];
      }
      else
      {
         header("HTTP/1.0 404 Not Found");
         header('location:'.$_SERVER["ASSIGN_PATH"].'404.shtml');
         exit;
      }

      $this->commonActions();

      /** Single result. */
      $this->wine_info = $this->model_wine->query_single_wine_id($this->wine_id);

      /** Set the wine results into minitemplator */
      $this->mini_t->setVariable('wine_info_wine_id', $this->wine_info['wine_id']);
      $this->mini_t->setVariable('wine_info_wine_name', $this->wine_info['wine_name']);
      $this->mini_t->setVariable('wine_info_year', $this->wine_info['year']);
      $this->mini_t->setVariable('wine_info_wine_type', $this->wine_info['wine_type']);
      $this->mini_t->setVariable('wine_info_winery_name', $this->wine_info['winery_name']);
      $this->mini_t->setVariable('wine_info_region_name', $this->wine_info['region_name']);
      $this->mini_t->setVariable('wine_info_on_hand', $this->wine_info['on_hand']);
      $this->mini_t->setVariable('wine_info_cost', $this->wine_info['cost']);

      /** Multiple results. */
      $this->wine_info_grapes = $this->model_grape_varity->search_wine_id($this->wine_id);
      foreach($this->wine_info_grapes as $rows)
      {
         /** Set grape varity results into minitemplator */
         $this->mini_t->setVariable('wine_info_variety', $rows['variety']);
         /** Put into block so that we can use foreach loop */
         $this->mini_t->addBlock("wine_info_variety_block");
      }

      $this->wine_info_orders = $this->model_orders->retrieve_orders($this->wine_id);
      foreach($this->wine_info_orders as $rows)
      {
         $this->mini_t->setVariable('wine_info_order_id', $rows['order_id']);
         $this->mini_t->setVariable('wine_info_date', $rows['date']);
         $this->mini_t->setVariable('wine_info_title', $rows['title']);
         $this->mini_t->setVariable('wine_info_firstname', $rows['firstname']);
         $this->mini_t->setVariable('wine_info_surname', $rows['surname']);
         $this->mini_t->setVariable('wine_info_address', $rows['address']);
         $this->mini_t->setVariable('wine_info_city', $rows['city']);
         $this->mini_t->setVariable('wine_info_state', $rows['state']);
         $this->mini_t->setVariable('wine_info_zipcode', $rows['zipcode']);
         $this->mini_t->setVariable('wine_info_country', $rows['country']);
         $this->mini_t->setVariable('wine_info_phone', $rows['phone']);
         $this->mini_t->setVariable('wine_info_birth_date', $rows['birth_date']);
         $this->mini_t->setVariable('wine_info_qty', $rows['qty']);
         $this->mini_t->setVariable('wine_info_price', $rows['price']);
         $this->mini_t->setVariable('wine_info_instructions', $rows['instructions']);
         $this->mini_t->addBlock("wine_info_pagination_block");
      }
   }

   /**
    * 404 missing action to redirect
    * users who are lost.
    *
    * @return void.
    */
   protected function _404Action()
   {
      $this->commonActions();
      $this->mini_t->setVariable('html_nxt_link', $this->html_nxt_link);
   }
}