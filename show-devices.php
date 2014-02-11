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
     <th><?php print( $deviceName ) ?></th>
     <td><?php print( $devicePin[0] ) ?></td>
     <td>
	<?php print( $devicePin[1] ) ?>
     </td>
     <td>
 	<?php print( $devicePin[2] ) ?>
     </td>
    </tr>
<?php
    }
?> 
   </table>
  </form>
