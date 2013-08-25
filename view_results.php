
   <form action="/assign1/partb/results.html" method="get">
      <div>
      Region: 
      <? Helpers::select("region",$this->region_results,array('region_id','region_name'),$this->region); ?>

      </div>
      <div>
      Grape Variety: 
      <? Helpers::select("grape_variety",$this->grape_variety_results,array('variety_id','variety'),$this->grape_variety,SELECT_ALL_TOP); ?>

      </div>
      <div>
      Wine Year: 
      <? Helpers::select("wine_year",$this->wine_year_results,array('year','year'),$this->wine_year,SELECT_ALL_TOP); ?>

      </div>
      <div>
      Search Wine: <input type="search" name="winesearch" value="<?= $this->winesearch; ?>">
      </div>
      <div>
      Search Winery: <input type="search" name="winerysearch" value="<?= $this->winerysearch; ?>">
      </div>
      <div>
      Min Cost: <input type="search" name="min_cost" value="<?= $this->min_cost; ?>" maxlength="6" size="6">
      Max Cost: <input type="search" name="max_cost" value="<?= $this->max_cost; ?>" maxlength="6" size="6">
      <?= $this->html_cost_error; ?>
      </div>
      <input type="submit">
   </form>
<?
if(isset($this->wine_results) && count($this->wine_results) > 1)
{
   ?>   <table>
      <thead>
         <tr>
            <th>Wine</th>
            <th>Grape Variety</th>
            <th>Year</th>
            <th>Wine Type</th>
            <th>Winery Name</th>
            <th>Region</th>
            <th>On Hand</th>
            <th>Cost</th>
            <th>Total<br />Stock Sold</th>
            <th>Total<br />Sales Revenue</th>
         </tr>
      </thead>
      <tfoot>
         <tr>
            <td><?= $this->html_prv_link; ?></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td style="text-align:right;"><?= $this->html_nxt_link; ?></td>
         </tr>
      </tfoot>
      <tbody style="text-align:center;"><?
   
   $cust_model = new ModelCustomer();
   foreach($this->wine_results as $row)
   {
      $region_row = Helpers::inArrays($this->region_results,$row['region_id'],'region_id');
      $variety_row = Helpers::inArrays($this->grape_variety_results,$row['variety_id'],'variety_id');

      /*
      $orders = array();
      foreach($this->orders_results as $order_row)
      {
         if($order_row['wine_id'] == $row['wine_id'])
         {
            $orders[] = $order_row + $this->cust_model->search_cust_id($order_row['cust_id']);
         }
      }
      */

      echo "\n";
      ?>
         <tr>
            <td><?= $row['wine_name']; ?></td>
            <td><?= $variety_row['variety']; ?></td>
            <td><?= $row['year']; ?></td>
            <td><?= $row['wine_type']; ?></td>
            <td><?= $row['winery_name']; ?></td>
            <td><?= $region_row['region_name']; ?></td>
            <td><?= $row['on_hand']; ?></td>
            <td>$<?= $row['cost']; ?></td>
            <td><?= $row['total_qty']; ?></td>
            <td>$<?= $row['total_price']; ?></td>
         </tr><?
   }
   ?>

      </tbody>
   </table><?
}
else
{
   ?><p>No records match your search criteria</p><?
   echo $this->html_nxt_link;
}

?>
