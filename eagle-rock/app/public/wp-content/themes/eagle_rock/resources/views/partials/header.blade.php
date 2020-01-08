<header class="header">
  <!-- MAIN MENU -->
        <div class="col-lg-12 row">
          <div class="main-menu__main">
            <div class="main-menu__main--logo">
              <a href="/"><img src='https://eagle-rock.local/wp-content/uploads/2019/12/eaglerock-logo-white.png' alt="logo"></a>
              <p>Eagle Rock Ministries</p>
            </div>
          <?php
            $desktopMainMenu = array(
              'theme_location' => 'primary_navigation',
              'menu_class' => 'nav',
              'level' => 1
            );

            if (has_nav_menu('primary_navigation')) :
              wp_nav_menu($desktopMainMenu);
            endif;
          ?>
          </div>
        </div>
</header>
