<div id="loginBlock" class="container" <?php if (!empty($login)) { ?> style="display: block" <?php } else { ?> style="display: none" <?php } ?>>
    <div class="row justify-content-center">
        <div class="col-6 ">
            <div class="text-center">
                <?php require 'logo.php'; ?>
            </div>
            <h4>Login</h4>
            <form id="loginForm" method="post">
                <div class="form-group">
                    <label class="text-normal text-dark mt-3">Email</label>
                    <input type="email" name="email" class="form-control mt-1" placeholder="name@email.com" value="">
                </div>
                <div class="form-group">
                    <label class="text-normal text-dark mt-3">Password</label>
                    <input type="password" name="password" class="form-control mt-1" placeholder="Password" value="">
                    <?php if (!empty($loginError)) { ?>
                        <p class="text-danger"><?php _e($loginError) ?></p>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <div>
                        <div class="peer mt-3">
                            <input type="hidden" id="login" name="login" value="login">
                            <button id="loginButton" class="btn btn-primary" type="submit">Login</button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="peer mt-3">
                Don't have an account? <span id="signupLink" class="link">Sign up</span>
            </div>
            <div class="peer">
                Forgot your password? <span id="resetLink" class="link">Reset Password</span>
            </div>
        </div>
    </div>
</div>