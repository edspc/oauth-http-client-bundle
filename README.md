# EdspcOauthHttpClientBundle
---
Example config
```yaml
edspc_oauth_http_client:
    default_auth: zoho
    auth:
        zoho:
            token_url: 'https://accounts.zoho.com/oauth/v2/token'
            client_id: '%env(ZOHO_CLIENT_ID)%'
            client_secret: '%env(ZOHO_CLIENT_SECRET)%'
    http_services:
        desk_client:
            base_uri: 'https://desk.zoho.com/'
        crm_client:
            base_uri: 'https://www.zohoapis.com/crm/v2/'

```
