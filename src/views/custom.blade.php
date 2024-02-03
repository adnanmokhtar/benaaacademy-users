<div class="form-group">
    <input name="gender"
           value="<?php echo @Request::old("gender", $user->gender); ?>"
           class="form-control input-lg"
           placeholder="gender"/>
</div>