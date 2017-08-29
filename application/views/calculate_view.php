
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
        <table class="table">
            <thead>
                <tr><th>Index</th><th>File name</th></tr>
            </thead>
            <tbody data-bind="foreach: filesArray">
                <tr>
                    <td data-bind="text: $index"></td>
                    <td data-bind="text: $data"></td>
                    <td><a class="btn btn-primary" data-bind="click: $parent.generateExcel">Generate</a></td>
                    <td><a class="btn btn-danger" data-bind="click: $parent.deleteFile">Delete</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>