<div id="signupBlock" class="container" <?php if (!empty($login)) { ?> style="display: none" <?php } ?>>
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="text-center">
                <?php require 'logo.php'; ?>
            </div>
            <h4>Register</h4>
            <form id="signupForm" method="post">
                <div class="form-group">
                    <label class="text-normal text-dark mt-3">Name</label>
                    <input type="text" name="name" class="form-control mt-1" Placeholder='John Doe'>
                </div>
                <div class="form-group">
                    <label class="text-normal text-dark mt-3">Email Address</label>
                    <input type="email" name="email" class="form-control mt-1" Placeholder='name@email.com'>
                </div>
                <div class="form-group">
                    <label class="text-normal text-dark mt-3">Password</label>
                    <input type="password" name="password" class="form-control mt-1" placeholder="Password">
                </div>
                <div class="form-group">
                    <label class="text-normal text-dark mt-3">Confirm Password</label>
                    <input type="password" name="confirm" class="form-control mt-1" placeholder="Password">
                    <?php if (!empty($signupError)) { ?>
                        <p class="text-danger"><?php _e($signupError) ?></p>
                    <?php } ?>
                </div>
                <div class="form-group mt-3">
                    <input type="hidden" id="signup" name="signup" value="signup">
                    <input id="signupButton" class="btn btn-primary" type="submit" value="Register">
                </div>
            </form>
            <div class="peer mt-3">
                Have an account? <span id="loginLink" class="link">Sign In</span>
            </div>
        </div>
    </div>
</div>