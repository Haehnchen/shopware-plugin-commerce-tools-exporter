# Shopware Commerce-Tools Exporter

Shopware plugin which mainly should export product and categories to Commerce-Tools

## Links

 - [commercetools.com](https://commercetools.com/)
 - [dev.commercetools.com](https://dev.commercetools.com/)
 - [admin.sphere.io](https://admin.sphere.io/)

## Requirements

 - Shopware >= 5.2
 - New Shopware Plugin System
 - Commerce-Tools Account with: project, client_id, client_secret, product_type_id
 - You should start a Commerce-Tools project with sample data eg. we need configured taxes and product types

### Custom attributes

Configurate custom attributes and fields

#### Products attributes

 - `external-id`
 - `seo-url`

## Command

Check credentials

```
 php bin/console ct:check:auth
```
 
Run data exporter
 
```
 php bin/console ct:exporter:categories
 php bin/console ct:exporter:products
```

Run commands twice for category and image relations
Use `-vvv` for debug output

## Done

 - category import with change set
 - initial product import with images

## TODOs

 - Product change set
 - ...
