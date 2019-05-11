# iPatrol
### Automate the control over the visitors of your MyBB site
---

## Implemented Features
- Track the location of your visitor and get various informations like: Country, City, Region, District, Timezone, Zipcode, Latitude-Longitude, Service Provider and many more.
- Automatically block the IP addresses of the visitors using proxy.
- Detect spiders visiting your site which are not already registered and showing as guests.
- Automatically add detected spiders to database so that they can show up in Who's online list as spiders with their name.
- Get notified about the detected spiders with a different User Agent string for further manual actions.
- Get notified about the automatic actions taken by iPatrol over email, PM, System log.

## Planned features so far
- Add an extra layer of security by using a honeypot for each form submission to attempt catching automation. The trapped bots can be further IP banned as well.
- Restrict / IP ban duplicate registration attempts.
- Restrict access for the visitors based on various geolocation conditions.
- Ability to see all actions performed by iPatrol right inside Moderator Control Panel.
- Automatically put the user account under moderation if new user (decided by making x legit posts) is posting a link. The moderation gets removed automatically after posting mentioned number of visible clean posts.

## External Dependency:
- [IP-API](http://ip-api.com/) for geo-location detection
- [CrawlerDetect](https://github.com/JayBizzle/Crawler-Detect) by @JayBizzle
