  <h2>Current Devices:</h2>

  <form method="POST">
   <table class="status">
    <tr>
       <td>Name</td>
       <td>Pin</td>
       <td>Exclusive</td>
       <td>Day Interval</td>
    </tr>
<?php
    foreach( $devices as $deviceName => $devicePin ) {
?>  
    <tr>
     <th align="left"><?php print( $deviceName ) ?></th>
     <td><?php print( $devicePin[0] ) ?></td>
     <td>
	<?php print( $devicePin[1] ) ?>
     </td>
     <td>
	<select name="<?php print($devicePin[0]) ?>-Interval" onChange="this.form.submit()">
	       <option value="1" <?= $devicePin[2]==1 ? "selected":""?>>Every day</option>
	       <option value="2" <?= $devicePin[2]==2 ? "selected":""?>>Every other day</option>
	       <option value="3" <?= $devicePin[2]==3 ? "selected":""?>>Every 3rd day</option>
	       <option value="4" <?= $devicePin[2]==4 ? "selected":""?>>Every 4th day</option>
	       <option value="5" <?= $devicePin[2]==5 ? "selected":""?>>Every 5th day</option>
	       <option value="6" <?= $devicePin[2]==6 ? "selected":""?>>Every 6th day</option>
	       <option value="7" <?= $devicePin[2]==7 ? "selected":""?>>Every 7th day</option>
	       <option value="8" <?= $devicePin[2]==8 ? "selected":""?>>Every 8th day</option>
	       <option value="9" <?= $devicePin[2]==9 ? "selected":""?>>Every 9th day</option>
	       <option value="10" <?= $devicePin[2]==10 ? "selected":""?>>Every 10th day</option>
	       <option value="15" <?= $devicePin[2]==15 ? "selected":""?>>Every 15th day</option>
	       <option value="20" <?= $devicePin[2]==20 ? "selected":""?>>Every 20th day</option>
      </select>
     </td>
    </tr>
<?php
    }
?> 
   </table>
  </form>
