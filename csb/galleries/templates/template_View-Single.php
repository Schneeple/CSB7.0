<?php
/**
 * Created by Grigori Burlea.
 * User: grigorib
 * Date: 2/22/20
 * Time: 99999.9 PM
 */

GLOBAL $db;

    $image_name = $_GET['image_name'];
    $img_id = $_GET['img_id'];

    $mysqli = mysqli_connect('localhost','csb','1password2ruleALL','csb');

    $query = "SELECT x, y, diameter, image_id FROM marks WHERE type='crater' AND image_id = ".$img_id;
//    $query = "SELECT x, y, diameter, details, type FROM marks WHERE (type='crater' or type='boulder') AND image_id = ".$img_id;

//    $image_data = mysqli_query($mysqli, $query);
    $image_data = $db->runBaseQuery($query);

    $x_values = array();
    $y_values = array();
    $diameter_values = array();

    // get image data
    foreach ($image_data as $imagedata) {
        // store x, y values into arrays
        $x_values[] = $imagedata['x'];
        $y_values[] = $imagedata['y'];
        $diameter_values[] = $imagedata['diameter'];
    }

    $query = "SELECT details FROM marks WHERE type='boulder' AND image_id = ".$img_id;
    $image_data = mysqli_query($mysqli, $query);
//    $image_data = $db->runBaseQuery($query);

    $x1_values = array();
    $y1_values = array();
    $x2_values = array();
    $y2_values = array();

    // get image data
    foreach ($image_data as $imagedata) {
        $details = json_decode($imagedata['details'], TRUE);
        // store x, y tuples into arrays
        $x1_values[] = $details['points'][0]['x'];
        $y1_values[] = $details['points'][0]['y'];
        $x2_values[] = $details['points'][1]['x'];
        $y2_values[] = $details['points'][1]['y'];
    }

    // get users' names and display them
    $query = "SELECT distinct marks.user_id, users.name FROM marks, users WHERE marks.image_id = ".$img_id ." AND marks.user_id = users.id";
    $user_data = mysqli_query($mysqli, $query);
    $user_ids = array();

    foreach ($user_data as $userdata) {
        $user_id = $userdata['name'];
        $user_ids[] = $user_id;
    }
?>

<script>
    function Main() {
        DrawCraters(0);
        DrawBoulders(0);
    }

    // Draw boulders recursive function
    function DrawBoulders(items_no) {
        // get arrays
        var x1_arr = <?php echo json_encode($x1_values); ?>;
        var y1_arr = <?php echo json_encode($y1_values); ?>;
        var x2_arr = <?php echo json_encode($x2_values); ?>;
        var y2_arr = <?php echo json_encode($y2_values); ?>;

        let flag = false;

        // base case
        if(items_no === (x1_arr.length - 1)) {
            flag = true;
        }

        var img = document.getElementById('user_img');
        var c = document.getElementById("myCanvas");
        var ctx = c.getContext("2d");

        // canvas for drawing. uses coordinates, sets the styles and draws
        ctx.beginPath();
        ctx.moveTo(x1_arr[items_no], y1_arr[items_no]);
        ctx.lineTo(x2_arr[items_no], y2_arr[items_no]);
        ctx.lineWidth = 2;
        ctx.strokeStyle = 'rgba(169, 30, 44, 0.4)';
        ctx.stroke();

        items_no++;

        if(flag === false) {
            DrawBoulders(items_no);
        }
    }

    // Draw craters recursive function
    function DrawCraters(items_no) {
        // get arrays
        var x_arr = <?php echo json_encode($x_values); ?>;
        var y_arr = <?php echo json_encode($y_values); ?>;
        var diameter_arr = <?php echo json_encode($diameter_values); ?>;

        let flag = false;

        // base case
        if(items_no === (x_arr.length - 1)) {
            flag = true;
        }

        var img = document.getElementById('user_img');
        var cnv = document.getElementById('myCanvas');
        var ctx = cnv.getContext("2d");

        // canvas for drawing. uses coordinates, sets the styles and draws
        cnv.style.position = "absolute";
        cnv.style.top = img.offsetTop + "px";
        cnv.style.left = img.offsetLeft + "px";

        ctx.beginPath();
        ctx.arc(x_arr[items_no], y_arr[items_no], diameter_arr[items_no] / 2, 0, Math.PI * 2, false);
        ctx.lineWidth = 2;
        ctx.strokeStyle = 'rgba(243, 199, 73, 0.9)';
        // ctx.strokeStyle = 'rgba(56, 70, 184, 0.8)';
        ctx.stroke();

        items_no++;

        if(flag === false) {
            DrawCraters(items_no);
        }
    }
</script>

<body onload="Main()">
    <div >
        <img alt="" id="user_img" src = "<?php echo $image_name?>"  />
        <canvas id="myCanvas" width="450px" height="450px"></canvas>
    </div>
</body>

<div class="image_markers">
    <h6>Users who marked this image</h6>
    <?php
        foreach ($user_ids as $userid) {
            ?><p><?php echo $userid?></p>
            <?php
        }
    ?>
</div>

<style>
    .image_markers p {
        margin-top: 5px;
        margin-bottom: 0px;
        font-size: .75em;
    }
    .image_markers h6 {
        margin-top: 10px;
    }
</style>
