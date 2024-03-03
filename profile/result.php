<?php
if(isset($_SESSION['id'])) {
    $connect = mysqli_connect("localhost", "root", "", "property");
    if(!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $pdquery = "";
    if(isset($_POST['dag'])){
        $puratondag = mysqli_real_escape_string($connect, $_POST['dag']);
    }
    if(strlen($puratondag) > 0 && $_POST['dagvalue'] == 0){
        $pdquery = "pdagno LIKE '%$puratondag,%' OR pdagno LIKE '%$puratondag' OR ";
    }
    $ndquery = "";
    if(isset($_POST['dag'])){
        $notundag = mysqli_real_escape_string($connect, $_POST['dag']);
    }
    if(strlen($notundag) > 0 && $_POST['dagvalue'] == 1){
        $ndquery = "dagno LIKE '%$notundag,%' OR dagno LIKE '%$notundag' OR ";
    }
    $pkquery = "";
    if(isset($_POST['khotiyan'])){
        $puratonkhotiyan = mysqli_real_escape_string($connect, $_POST['khotiyan']);
    }
    if(strlen($puratonkhotiyan) > 0 && $_POST['khotiyanvalue'] == 0){
        $pkquery = "pkhatian LIKE '%$puratonkhotiyan,%' OR pkhatian LIKE '%$puratonkhotiyan' OR ";
    }
    $nkquery = "";
    if(isset($_POST['khotiyan'])){
        $notunkhotiyan = mysqli_real_escape_string($connect, $_POST['khotiyan']);        
    }
    if(strlen($notunkhotiyan) > 0 && $_POST['khotiyanvalue'] == 1){
        $nkquery = "khatian LIKE '%$notunkhotiyan,%' OR khatian LIKE '%$notunkhotiyan' OR ";
    }
    $dquery = "";
    if(isset($_POST['dolil'])){
        $dolil = mysqli_real_escape_string($connect, $_POST['dolil']);
        $dquery = "dnum = '$dolil' OR ";
    }
    $mquery = "";
    if(isset($_POST['mouja'])){
        $mouja = mysqli_real_escape_string($connect, $_POST['mouja']);
        $mquery = "mouja = '$mouja'";
    }

    $table = "user".$_SESSION['id'];
    $query = "SELECT * FROM $table WHERE " . $pdquery . $ndquery . $pkquery . $nkquery . $dquery . $mquery . ";";

    $data = mysqli_query($connect, $query);
    if(mysqli_num_rows($data) > 0) {
    ?>
        <table class="table table-bordered">
            <caption style="text-align:center;padding-top:0px;">
                <h4> Result of your inquired information </h4>
            </caption>
            <thead>
                <tr>
                    <th> NO </th>
                    <th> Dolil NO </th>
                    <th> Dag No </th>
                    <th> puraton dag No </th>
                    <th> Khotiyan No </th>
                    <th> Puraton khotiyan No </th>
                    <th> Old Owner </th>
                    <th> Area of land (cent) </th>
                </tr>
            </thead>
            <tbody>
            <?php
            while($row = mysqli_fetch_array($data)) {
            ?>
                <tr>
                    <td> <?php echo $row['UID'] ?> </td>
                    <td> <?php echo $row['dnum'] ?> </td>
                    <td> <?php echo $row['dagno'] ?> </td>
                    <td> <?php echo $row['pdagno'] ?> </td>
                    <td> <?php echo $row['khatian'] ?> </td>
                    <td> <?php echo $row['pkhatian'] ?> </td>
                    <td> <?php echo $row['oldowner'] ?> </td>
                    <td> <?php echo $row['size'] ?> </td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    <?php
    }
    else {
        echo "<h4 class='text-center'> There is no data available about your inquiry! </h4>";
    }
}
else {
    header("location:http://localhost/Property-Management/auth");
    exit;
}
?>
