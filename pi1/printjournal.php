<html>
<head>
<style>
    <!--
	.tx-wecjournal-printBtn {
		text-align: 	center;
	    text-decoration: none;
		font-family: 	 "Trebuchet MS", Tahoma, Georgia, Verdana, Arial,Sans-serif;
		font-size: 		12px;
		font-weight:	normal;
		padding-left:  1px;
		padding-right: 3px;
		margin:  		3px;
	    color: 			#303010;
	    height: 		20px;
	    background-color: #D0D0E0;
	}
	.tx-wecjournal-printBtnHov {
	    background-color: #E8E890;
	 	text-decoration:    none;
	}
    -->
</style>
<script language="Javascript">
<!--
function padout(number) { return (number < 10 && (String(number).charAt(0) != '0')) ? String('0' + number) : String(number); }
function isInt(elm) {
    for (var i = 0; i < elm.length; i++) {
        if (elm.charAt(i) < "0" || elm.charAt(i) > "9") {
            return false;
        }
    }
    return true;
}
function chooseAndClose(whichAction)
{
	today = new Date();
	thisMonth = today.getMonth(); thisMonth++;
	enddate = padout(thisMonth) + padout(today.getDate()) + padout(today.getFullYear());
	switch (whichAction)
	{
		case 1: // TODAY
			startdate = enddate;
			break;
		case 2: // THIS WEEK
			thisWeek = new Date(today.getYear(), today.getMonth(), today.getDate() - today.getDay());
			thisWeekEnd = new Date(today.getYear(), today.getMonth(), today.getDate() - today.getDay() + 7);
			startdate = padout(thisWeek.getMonth()+1)+padout(thisWeek.getDate())+padout(thisWeek.getFullYear());
			enddate   = padout(thisWeekEnd.getMonth()+1)+padout(thisWeekEnd.getDate())+padout(thisWeekEnd.getFullYear());
 			break;
 		case 3: // THIS MONTH
			thisMonth = new Date(today.getYear(), today.getMonth(), 1);
			thisMonthEnd = new Date(today.getYear(), today.getMonth(), 31);
			startdate = padout(thisMonth.getMonth()+1)+padout(thisMonth.getDate())+padout(thisMonth.getFullYear());
			enddate = padout(thisMonthEnd.getMonth()+1)+padout(thisMonthEnd.getDate())+padout(thisMonthEnd.getFullYear());
 			break;

 		case 5: // ALL
	 		first = new Date(today.getYear()-5, 1, 1);
			startdate = padout(first.getMonth())+padout(first.getDate())+padout(first.getFullYear());
			now = new Date(today.getYear(), today.getMonth(), today.getDate());
			endddate = padout(now.getMonth())+padout(now.getDate())+padout(now.getFullYear());
 			break;
	}
	opener.doprint(startdate,enddate);
	window.close();
}

-->
</script>
</head>
<body style="margin:0px">
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgColor="#F0F0F8">
  <tr>
    <td><div style="border-bottom:2px single #404040;font-weight:bold;background-color:#C0C0C8;padding:6px;text-align:center;">Print Journal Options</div></td>
  </tr>
  <tr>
    <td>
      <FORM NAME="printjournal" method="post" action="<?=$_SERVER['PHP_SELF']?>&doPrint=1">
        <input type="hidden" name="printaction" value="0">
        <input type="hidden" name="printstart" value="0">
        <input type="hidden" name="printend" value="0">
        <p align="center">
          <font size="2">What journal entries would you like to print?
            <br/>
            <br/>
       	  	<input TYPE="button" class="tx-wecjournal-printBtn" style="width:150px;" name="Today" VALUE="Today" onClick="chooseAndClose(1);" onMouseOver="this.className='tx-wecjournal-printBtn tx-wecjournal-printBtnHov'" onMouseOut="this.className='tx-wecjournal-printBtn'">
       	  	<br/>
   	    	<input TYPE="button" class="tx-wecjournal-printBtn" style="width:150px" name="ThisWeek" VALUE="This Week" onClick="chooseAndClose(2);" onMouseOver="this.className='tx-wecjournal-printBtn tx-wecjournal-printBtnHov'" onMouseOut="this.className='tx-wecjournal-printBtn'">
   	    	<br/>
          	<input TYPE="button" class="tx-wecjournal-printBtn" style="width:150px" name="ThisMonth" VALUE="This Month" onClick="chooseAndClose(3);" onMouseOver="this.className='tx-wecjournal-printBtn tx-wecjournal-printBtnHov'" onMouseOut="this.className='tx-wecjournal-printBtn'">
          	<br/>
          	<input TYPE="button" class="tx-wecjournal-printBtn" style="width:150px" name="All" VALUE="All" onClick="chooseAndClose(5);" onMouseOver="this.className='tx-wecjournal-printBtn tx-wecjournal-printBtnHov'" onMouseOut="this.className='tx-wecjournal-printBtn'">
          	<br/>
          	<br/>
          	<input TYPE="button" class="tx-wecjournal-printBtn" VALUE="CANCEL" onClick="window.close();" onMouseOver="this.className='tx-wecjournal-printBtn tx-wecjournal-printBtnHov'" onMouseOut="this.className='tx-wecjournal-printBtn'">
          </font>
      </FORM>
    </td>
  </tr>
</table>
</body>
</html>
