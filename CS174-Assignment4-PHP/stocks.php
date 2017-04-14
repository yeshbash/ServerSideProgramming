<html>
<head>
  <style>

    body{
      font-family: verdana;
      text-align: center;
      font-size: 14px;
    }
    div{
      margin:auto;
      background-color: whitesmoke;
      border-style: solid;
      border-radius: 3px;
      border-width: thin;
    }

    table,th,td{
      border-collapse: collapse;
      border: 1px solid black;
      margin: auto;
      font-size: 14px;
    }

    th,td{
      padding-left: 5px;
      padding-top: 5px;
      padding-bottom: 5px;
      padding-right: 10px;
      text-align: left;
    }
    th{
      background-color: ghostwhite;
    }

   td{
     background-color: whitesmoke;
   }

   hr{
     border
   }

    .status_div{
      text-align: center;
      width: 500px;
      padding:5px;
    }
    .search_div{
      width: 30%;
      margin-top: 50px;
      text-align: center;
    }

    .btn{
      margin-top: 5px;
      margin-bottom: 5px;
      border-radius: 3px;
      background-color: white;
    }

    .search_title{
      font-size: 30px;
      font-style: italic;
      font-weight: bold;
      font-family: times new roman,cursive;
      letter-spacing: 1px;
      margin:5px;

    }

    td.quote_data{
      text-align: center;
      background-color: snow;
    }

    .change_icon{
      width: 15px;
      height: 15px;
      margin-left: 2px;
    }

    td.quote_header{
      font-weight: bold;
    }

  </style>
  <script type="text/javascript">
    function validateSearchBox(){
      if(!document.getElementById("search_text").value)
        return false;
      return true;
    }

    function onClear(){
      document.getElementById("search_text").value="";

      var status_div = document.getElementById("status_div");
      var searchres_table = document.getElementById("searchres_table");
      var quote_table = document.getElementById("quote_table");

      if(status_div)  status_div.style.display="none";
      if(searchres_table) searchres_table.style.display="none";
      if(quote_table) quote_table.style.display="none";

    }
  </script>
<?php

$LOOKUP_BASEURL = "http://dev.markitondemand.com/MODApis/Api/v2/Lookup/xml?input=";
$QUOTE_BASEURL = "http://dev.markitondemand.com/MODApis/Api/v2/Quote/json?symbol=";
$search_text="";

if(isset($_REQUEST["search_text"])){
  $GLOBALS['search_text'] = htmlspecialchars($_REQUEST["search_text"]);
}

function format_marketcap($market_cap){
  $result = 0.0;
  if(!empty($market_cap))
    $result = round($market_cap/1000000000,2);
  return $result." B";
}

function format_change_YTD($price, $changeTYD){
  $result = round($price - $changeTYD,2);
  if($result <0){
    echo '('.$result.')';
  }else{
      echo $result;
  }

  display_change_icon($result);
}

function display_change_icon($value){
  if($value>0)
    echo "<img class='change_icon' src='green.svg'/>";
  else if($value<0)
    echo "<img class='change_icon' src='red.png'/>";
}

function make_http_request($url){
  $curl_session = curl_init();
  curl_setopt($curl_session,CURLOPT_URL,$url);
  curl_setopt($curl_session,CURLOPT_RETURNTRANSFER,1);
  $retValue = curl_exec($curl_session);
  curl_close($curl_session);
  return $retValue;

}

function handle_stock_search($search_text){
  if(!empty($search_text)){
      $sResp  = make_http_request($GLOBALS["LOOKUP_BASEURL"]. urlencode($search_text));
      $xResp = new SimpleXMLElement($sResp);
      #print_r($xResp);

      if(!isset($xResp->LookupResult)){
        echo "<div class='status_div' id='status_div'><p> No records found for the search term</p></div>";
      }else{
        echo "<table class='quote_table' id='quote_table'>";
        echo "<th>Name</th><th>Symbol</th><th>Exchange</th><th>Details</th>";
        foreach ($xResp->LookupResult as $resultItem) {
          echo "<tr>";
          echo "<td>".$resultItem->Name."</td>";
          echo "<td>".$resultItem->Symbol."</td>";
          echo "<td>".$resultItem->Exchange."</td>";
          $url = "stocks.php?stock_sym=".urlencode($resultItem->Symbol)."&form_action=stock_quote&search_text=".urlencode($GLOBALS['search_text']);
          echo "<td><a href=".$url.">More Info</a></td>";
        }
      echo "</table></div>";
    }
  }
}

function handle_stock_quote($stock_symbol){
  if(!empty($stock_symbol)){
    $sResp  = make_http_request($GLOBALS["QUOTE_BASEURL"].urlencode($stock_symbol));
    $jResp = json_decode($sResp);
    if($jResp->Status == "SUCCESS"){
    ?>
    <table class='searchres_table' id='searchres_table'>
        <tr><td class="quote_header">Name</td><td class="quote_data"><?php echo $jResp->Name?></td></tr>
        <tr><td class="quote_header">Symbol</td><td class="quote_data"><?php echo $jResp->Symbol?></td></tr>
        <tr><td class="quote_header">Last Price</td><td class="quote_data"><?php echo $jResp->LastPrice?></td></tr>
        <tr><td class="quote_header">Change</td><td class="quote_data"><?php echo round($jResp->Change,2); display_change_icon($jResp->Change);?></td></tr>
        <tr><td class="quote_header">Change Percent</td><td class="quote_data"><?php echo round($jResp->ChangePercent,2)."%"; display_change_icon($jResp->ChangePercent);?></td></tr>
        <tr><td class="quote_header">Timestamp</td><td class="quote_data"><?php echo date("Y-m-d h:i A",strtotime($jResp->Timestamp))?></td></tr>
        <tr><td class="quote_header">Market Cap</td><td class="quote_data"><?php echo format_marketcap($jResp->MarketCap)?></td></tr>
        <tr><td class="quote_header">Volume</td><td class="quote_data"><?php echo number_format($jResp->Volume,0,'',',')?></td></tr>
        <tr><td class="quote_header">Change YTD</td><td class="quote_data"><?php format_change_YTD($jResp->LastPrice,$jResp->ChangeYTD)?></td></tr>
        <tr><td class="quote_header">Change Percent YTD</td><td class="quote_data"><?php echo round($jResp->ChangePercentYTD,2)."%";display_change_icon($jResp->ChangePercentYTD);?></td></tr>
        <tr><td class="quote_header">High</td><td class="quote_data"><?php echo $jResp->High?></td></tr>
        <tr><td class="quote_header">Low</td><td class="quote_data"><?php echo $jResp->Low?></td></tr>
        <tr><td class="quote_header">Open</td><td class="quote_data"><?php echo $jResp->Open?></td></tr>
      </table>
    </div>
    <?php }else{
        echo "<div class='status_div' id='status_div'><p> There is no stock information available</p></div>";
    }
  }
}
?>

<body>
  <div class="search_div">
    <p class="search_title">Stock Search</p><hr width="90%">
    <form action = <?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> id="stock_search" name="stock_search" method="post">
      Company Name or Symbol : <input id="search_text" name="search_text" type="text" value="<?php echo $GLOBALS["search_text"] ?>"/>
      <input type="hidden" name="form_action" value="stock_search"/></br>
      <input type="button" class = "btn" value="Search" onclick="if(validateSearchBox()) document.getElementById('stock_search').submit(); else window.alert('Enter Company Name or Symbol');"/>
      <input type="button" class = "btn" value="Clear" onclick="onClear();"/><br>
      <a href="http://www.markit.com/product/markit-on-demand">Powered by Markit on Demand</a>
    </form>
  </div>
  <br><br>
  <?php
    if(isset($_POST["form_action"]) && $_POST["form_action"] == "stock_search") {
        handle_stock_search($_POST["search_text"]);
    }else if(isset($_GET["form_action"]) && $_GET["form_action"]=="stock_quote") {
        handle_stock_quote($_GET["stock_sym"]);
  }?>
</body>
</html>
