<h1>On this page calculation will be done</h1>

<div class="row">   
    <div class="col-md-7">
        <?php echo form_open_multipart('Welcome/do_upload');?>

            <input type="file" name="userfile" size="20"/>

            <br /><br />

            <input class="btn btn-info" type="submit" value="Upload file" />

        </form>
    </div>
    <div class="col-md-5">
        <?php
        echo "<div class='col-md-12'><p>Click name to generate data</button></p>";
        if ($handle = opendir('./uploads/')) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    echo "<div class='col-md-6'><button class='btn btn-primary' data-bind='click:readExcel'>$entry</button></div>"
                         ."<div class='col-md-6'>"
                         . "<button class='btn btn-danger'>Delete</button>"
                         . "</div>";
                }
            }
            closedir($handle);
        }   
        ?>
    </div>
</div>