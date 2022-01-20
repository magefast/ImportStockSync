

# Magento 2 module - Strekoza_ImportStockSync


 **Import Stock/Price data (qty, stock status), update Price data**

<br/>

- Import Stock/Price data from CSV file.
- After update Products data - will runned reindex.
- Update for few thouthands of Products can take up 3 minute, much time take reindex.

<br/>

#### Run import with CLI
```sh
php bin/magento sync:stock
```

<br/>

#### Sync by Cron
* Added sync with cron job rule at 5 AM.

<br/>

#### Run sync also can via URL link
* http://magento.local/importdocksync

