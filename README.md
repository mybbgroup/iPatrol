# iPatrol
Automate the control over the visitors of your MyBB site
---

# NOTE: UNDER DEVELOPMENT // DO NOT USE IN LIVE SITE
This plugin will help you automatically control the visitors of your site based on their IP address.

## Implemented Features
- Track the location of your visitor and get various informations like: Country, City, Region, District, Timezone, Zipcode, Latitude-Longitude, Service Provider and many more.
- Automatically block the IP addresses of the visitors using proxy.
- Detect spiders visiting your site which are not already registered and showing as guests.
- Automatically add detected spiders to database so that they can show up in Who's online list as spiders with their name.
- Get notified about the detected spiders with a different User Agent string for further manual actions.
- Get notified about the automatic actions taken by iPatrol over email, PM, System log.
- 
## Planned features so far
- Restrict / IP ban duplicate registration attempts.
- Restrict access for the visitors based on various geolocation conditions.

## Major todo / fixes:
- Bot detection log table in ACP
- Front end (js) API cache

## External Dependency:
[IP-API]() for geo-location detection
[CrawlerDetect](https://github.com/JayBizzle/Crawler-Detect) by @JayBizzle