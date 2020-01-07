# Magento Data Services

The **Magento Data Services Module** is responsible for brokering data needed to train machine learning models and build out Magento data-driven features such as Product Recommendations.


## Documentation
Please use this [link](docs) to access the latest Product Recommendations documentation.


## Installation

Clone the repository into your **Magento** installation under the `app/code/Magento/DataServices` directory.

```bash
cd <magento directory>/app/code/Magento
git clone git@github.com:magento/data-services.git
```

Refresh the **Magento** instance for the module to take effect.

```bash
cd <magento directory>
php bin/magento module:enable Magento_DataServices
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
```

