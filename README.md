# a simple REST service for a simple shopping experience.
#!!!warning this is created as a coding execise, do not use in production!!!
This is created for specific task and there is quite a few assumptions



### User shopping experience features:
- Manage (add, remove & update) product stock at any time
- Manage user cart by adding or removing products
- Get cart subtotal, vat amount and total at any time

### How to use
####to begin
git clone
composer install
bin/console cache:clear --no-warmup
bin/console cache:warmup
bin/console doctrine:schema:update --force

####for tests run 

vendor/bin/phpunit

####to run
bin/console server:start

####to stop
bin/console server:stop


tested on php 7.3.9 on manjaro linux.
#### API doc located on localserver:
 /api/doc

###Requirements:
php 7.3 (might work on 7.2, not tested), with pdo_sqlite, curl, iconv extensions, maybe something else 

##Assumptions
- cart is just a cart, no additional info stored here, any logic to tie chart to a customer is done elsewhere
- price is stored as 2 integers to support ui side of this
- ui that is not implemented here does not suffer from xss (inserting product with name "<script>alert('3v1l h4ck!')</script>" is handled)
- no access control is done here, assumed to be available for correct use, putting this on public network equals being modifiable by unauthorised 3rd parties, else access should be checked for 2 roles -user that can create carts and stock manager that can manage stock
- when checkout process is done, cart is deleted, order tracking is done elsewhere
- stale/forgotten/abandoned carts wil be deleted at some point in time from elsewhere, this system does bother to do this
- there will never be a lot of products (no need to paginate product list, no need to paginate cart contents)
- user can add products that are out of stock to cart
- product prices are only positive values (no product can be "halloween special discount, -9.99€")
- user can buy several items of the same product
- product quantity is sold by whole unit, quantity always integer,  no product sold such as "sugar, 0.75 kg";
- product quantity is in single unit type (peace?), products are not using different units 
- product quantity is positive integer, no product returns implemented via this system (no order line "cool socks, tried, didn't fit, quantity: -4")
- no need to list all carts
##Potential problems
- for simplicity this system calculates VAT using floats, while not very likely, this can introduce float related precision problems.. (more than once I have spent time debugging/resolving such issues, not pleasant)
- current design uses floats for both - storage of VAT and in calculation process of VAT, both are places that can introduce float precision related errors, one solution to this would be to use decimal values
- rounding of money - uses half up mode, before release should be specified if this behaviour is correct


##Design considerations
- the shop operates in single country, single currency/tax regime