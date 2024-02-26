<?php
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $pdquery = "";
        if(isset($_POST['dag']) && $_POST['dagvalue'] == 0){
            $puratondag = $_POST['dag'];
            $pdquery = "pdagno LIKE '%$puratondag%' OR ";
        }
        $ndquery = "";
        if(isset($_POST['dag']) && $_POST['dagvalue'] == 1){
            $notundag = $_POST['dag'];
            $ndquery = "dagno LIKE '%$notundag%' OR ";
        }
        $pkquery = "";
        if(isset($_POST['khotiyan']) && $_POST['khotiyanvalue'] == 0){
            $puratonkhotiyan = $_POST['khotiyan'];
            $pkquery = "pkhatian LIKE '%$puratonkhotiyan%' OR ";
        }
        $nkquery = "";
        if(isset($_POST['khotiyan']) && $_POST['khotiyanvalue'] == 1){
            $notunkhotiyan = $_POST['khotiyan'];
            $nkquery = "khatian LIKE '%$notunkhotiyan%' OR ";
        }
        $dquery = "";
        if(isset($_POST['dolil'])){
            $dolil = $_POST['dolil'];
            $dquery = "dnum = '$dolil' OR ";
        }
        $mquery = "";
        if(isset($_POST['mouja'])){
            $mouja = $_POST['mouja'];
            $mquery = "mouja = '$mouja'";
        }
        $connect = mysqli_connect("localhost", "root", "", "property");
        if (!$connect) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $table = "user".$_SESSION['id'];
        $query = "SELECT * FROM $table WHERE " . $pdquery . $ndquery . $pkquery . $nkquery . $dquery . $mquery . ";";
        echo $query;

        $data = mysqli_query($connect, $query);
        if(mysqli_num_rows($data) > 0) {
            echo "ok";
            while($row = mysqli_fetch_array($data)) {
?>
                <table class="table table-bordered">
                    <caption style="text-align:center;padding-top:0px;">
                        <h4> All saved measurement </h4>
                    </caption>
                    <thead>
                        <tr>
                            <th> NO </th>
                            <th> Dolil NO </th>
                            <th> Dag No </th>
                            <th> Khotiyan No </th>
                            <th> Old Owner </th>
                            <th> Area of land (cent) </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td> <?php echo $row['UID'] ?> </td>
                            <td> <?php echo $row['dnum'] ?> </td>
                            <td> <?php echo $row['dagno'] ?> </td>
                            <td> <?php echo $row['khatian'] ?> </td>
                            <td> <?php echo $row['oldowner'] ?> </td>
                            <td> <?php echo $row['size'] ?> </td>
                        </tr>
                    </tbody>
                </table>
<?php
            }
        }
        else {
            echo "<h4 class='text-center'> There is no data available about your inquiry! </h4>";
        }
    }
?>
