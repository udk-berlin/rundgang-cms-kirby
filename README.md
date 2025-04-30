<img src="http://getkirby.com/assets/images/github/plainkit.jpg" width="300">

**Kirby: the CMS that adapts to any project, loved by developers and editors alike.**
The Plainkit is a minimal Kirby setup with the basics you need to start a project from scratch. It is the ideal choice if you are already familiar with Kirby and want to start step-by-step.

You can learn more about Kirby at [getkirby.com](https://getkirby.com).

### Try Kirby for free

You can try Kirby and the Plainkit on your local machine or on a test server as long as you need to make sure it is the right tool for your next project. … and when you’re convinced, [buy your license](https://getkirby.com/buy).

### Get going

Read our guide on [how to get started with Kirby](https://getkirby.com/docs/guide/quickstart).

You can [download the latest version](https://github.com/getkirby/plainkit/archive/main.zip) of the Plainkit.
If you are familiar with Git, you can clone Kirby's Plainkit repository from Github.

    git clone https://github.com/getkirby/plainkit.git

## What's Kirby?

- **[getkirby.com](https://getkirby.com)** – Get to know the CMS.
- **[Try it](https://getkirby.com/try)** – Take a test ride with our online demo. Or download one of our kits to get started.
- **[Documentation](https://getkirby.com/docs/guide)** – Read the official guide, reference and cookbook recipes.
- **[Issues](https://github.com/getkirby/kirby/issues)** – Report bugs and other problems.
- **[Feedback](https://feedback.getkirby.com)** – You have an idea for Kirby? Share it.
- **[Forum](https://forum.getkirby.com)** – Whenever you get stuck, don't hesitate to reach out for questions and support.
- **[Discord](https://chat.getkirby.com)** – Hang out and meet the community.
- **[Mastodon](https://mastodon.social/@getkirby)** – Spread the word.
- **[Instagram](https://www.instagram.com/getkirby/)** – Share your creations: #madewithkirby.

---

© 2009 Bastian Allgeier
[getkirby.com](https://getkirby.com) · [License agreement](https://getkirby.com/license)

## Custom API Endpoints

The following custom API endpoints are provided to extend the functionality of the Kirby CMS for the annual Rundgang frontend. These endpoints are located in the `site/plugins/api` folder.

---

### **GET** `/api/2025/contexts`

**Description:**
Returns a nested JSON object with all available contexts of 2025.

---

### **GET** `/api/2025/formats`

**Description:**
Returns a JSON object with all available formats of 2025.

**Response Example:**

```json
[
    {
        "key": "demo-project",
        "en": "Demo Project",
        "de": "Demo Projekt"
    },
    {
        "key": "demo-project-2",
        "en": "Demo Project 2",
        "de": "Demo Projekt 2"
    },
    ...
]
```

---

### **GET** `/api/2025/locations`

**Description:**
Returns a JSON object with all available locations of 2025.

**Response Example:**

```json
[
    {
        "name": "UNI.T - Theater der UdK Berlin",
        "street": "Fasanenstraße 1b",
        "postcode": "10623",
        "city": "Berlin",
        "latitude": "52.5093475",
        "longitude": "13.3276388"
    },
    {
        "name": "Konzertsaal",
        "street": "Hardenbergstraße, Fasanenstraße 33 (Ecke)",
        "postcode": "10623",
        "city": "Berlin",
        "latitude": "52.5092561",
        "longitude": "13.3275879"
    },
    ...
]
```
