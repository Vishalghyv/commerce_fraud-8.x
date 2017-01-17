# commerce_fraud

This module provides a framework to detect potentially fraudulent
orders and act on this.

This module provides:

- 1 Rules event: "Fraud count changed"
- 5 Rules conditions:
    - Order has fraud level
    - Order has PO Box
    - The given country matches the users IP address
    - The users IP address is in specified countries
    - Order placed within x minutes from most recent order
- 3 Rules actions:
    - Increase the fraud count
    - Decrease the fraud count
    - Reset the fraud count
- 6 Default Rules:
    - Change order status depending on Fraud level
    - Increase Fraud Score if Order has X products
    - Increase Fraud Score if User IP in Selected Countries (must have `ip2country` module installed)
    - Increase Fraud Score for Orders with PO Box
    - Increase Fraud Score for expensive Orders
    - Increase Fraud Score for Orders placed within X minutes

The Rules actions will increase the fraud score on the provided
`commerce_order`. This counter is an integer that the "Reset the fraud
count" action sets back to 0. The "Increase the fraud count" and
"Decrease the fraud count" actions will increase or decrease this
fraud count, by default by 1. (But this is customizable.)

The "Fraud count changed" event is fired every time one of the actions
is called.

The limits used by the 3 conditions are configurable in
/admin/commerce/config/fraud. By default, they are:

- An order is whitelisted if the fraud count is < 10
- An order is blacklisted if the fraud count is >= 20  
- An order is greylisted if it's between 10 and 20

This module provides some default rules that can be turned on to start 
keeping track of potentially fraudulent orders.

Developed by [Acro Media Inc][0], Sponsored by [Skilld.fr][1] and also developed by [Commerce Guys][2].


  [0]: http://www.acromediainc.com
  [1]: http://www.skilld.fr
  [2]: https://commerceguys.com
