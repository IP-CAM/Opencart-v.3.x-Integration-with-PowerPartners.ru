# Module for integration of OpenCart v3.x and PowerPartners.ru

The module adds products of the affiliate program [PowerPartners.ru] (http://powerpartners.ru/) to the assortment of a store running on the OpenCart v3.x engine, synchronizes availability, prices, descriptions, product images, certificates and technical data sheets. Transfers accepted orders to the affiliate program, as well as synchronizes order statuses.

## System requirements

* Online store on the [OpenCart] engine (https://www.opencart.com/) v3.x (tested on the Russian assembly [opencart-russia.ru] (http://opencart-russia.ru/))
* Ability to install on the hosting / server [cron jobs] (https://ru.wikipedia.org/wiki/Cron)

## Installing the module

1. Download the current release of the powerpartners.ocmod.zip module from the "[Releases] (https://github.com/powerpartners/opencart-mod/releases/latest)" section
2. Go to the admin panel of your OpenCart store
3. Go to the section Modules / Extensions -> Install extensions. Click the "Download" button and select the previously downloaded file [powerpartners.ocmod.zip] (https://github.com/powerpartners/opencart-mod/raw/master/powerpartners.ocmod.zip). Wait until the installation process is complete.
4. Go to the Modules / Extensions -> Modules / Extensions section and activate the PowerPartners module
5. Go to the Modules / Extensions -> Modifiers section and update the modifier cache by clicking the "Refresh" button in the upper right corner.
6. Return to the Modules / Extensions -> Modules / Extensions section and enter the settings of the PowerPartners module, press the "Edit" button
   - Specify the "API Key" that was generated for your domain (or received upon request to technical support)
   - Select "Enabled" status for synchronization
   - If necessary, select the correspondence of the statuses of OpenCart orders to the statuses of PowerPartners
   - If necessary, select the correspondence of product statuses (available / not available)
   - Select the correspondence between PowerPartners weight and size units (kg and mm) and OpenCart units
   - Copy the cron task settings lines located in the information block at the top of the module settings window
7. Configure on the hosting / server cron jobs obtained in the previous paragraph

### cron jobs

cron jobs are commands that are executed at a specified frequency. It is cron jobs that directly synchronize the data of the store and the affiliate program, and without them, the module cannot work.

Setting up cron jobs is done on each hosting in its own way, there are no general recommendations, therefore, in case of difficulties, it is recommended to contact the hosting technical support with a request to help set up cron jobs. 
