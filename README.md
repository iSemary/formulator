composer install

doctrine:fixtures:load

symfony serve

## Convert SCSS to CSS

sass --watch public/dashboard/css/main.scss:public/dashboard/css/main.css
