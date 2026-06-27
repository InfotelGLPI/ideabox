# Ideabox Plugin — Documentation

## Overview

The **Ideabox** plugin for GLPI lets you collect, manage, and track ideas submitted by end users. It provides a voting system, commenting, and notifications to engage the entire user base in a continuous improvement process.

- **Version**: 4.0.9
- **GLPI compatibility**: 11.0.x
- **License**: GPLv3+
- **Authors**: Xavier CAILLAUD, Infotel

---

## Features

- Idea submission by users (central interface and helpdesk)
- **Voting system**: each user can vote or cancel their vote on an idea
- **Comments** on ideas, with moderation by the author
- **Idea lifecycle**: New → In Study → In Progress → Closed
- **E-mail notifications**: idea creation, update, deletion, new comments
- **Fuzzy search** in the helpdesk interface via Fuse.js
- Association of ideas with **GLPI tickets**
- **Profile-based rights** management
- Compatibility with the **Service Catalog** plugin
- Compatibility with the **Data Injection** plugin
- GLPI **entity** scoping support
- **Multilingual translations** of the configuration (title and description of the idea box)

---

## Installation

1. Download the archive from [GitHub Releases](https://github.com/InfotelGLPI/ideabox/releases).
2. Extract it into the `plugins/` directory of your GLPI installation.
3. Log in to GLPI as an administrator.
4. Go to **Configuration → Plugins** and activate **Ideabox**.

---

## Configuration

### Profile rights

In **Administration → Profiles**, an **Ideas** tab appears on each profile.

| Right | Description |
|---|---|
| `plugin_ideabox` | Full access to ideas (read, create, update, delete) |
| `plugin_ideabox_open_ticket` | Allow ideas to be associated with tickets |

Standard GLPI rights apply: `READ`, `CREATE`, `UPDATE`, `DELETE`, `PURGE`.

### General settings

Under **Ideabox → Configuration**, two fields are available:

| Field | Description |
|---|---|
| **Title** | Heading displayed above the idea list (translatable per language) |
| **Comment** | Description or tagline shown to users (translatable per language) |

Configuration supports **per-language translations** via the **Translations** tab (table `glpi_plugin_ideabox_configtranslations`).

---

## Usage

### Submitting an idea

1. Go to **Tools → Ideas** (central interface) or the **Ideabox** entry in the helpdesk menu.
2. Click **Add**.
3. Fill in:
   - **Name** *(required)*: short title for the idea
   - **Description** *(required)*: detailed description (TinyMCE rich editor)
4. Save. An e-mail notification is sent if notifications are enabled.

> In the helpdesk interface, only the original author can edit their own idea. Administrators can edit any idea from the central interface.

### Voting on an idea

In the idea list, click the **👍** button to cast a vote. Clicking again cancels the vote. Each user can vote only once per idea.

### Commenting on an idea

On an idea's detail page, go to the **Comments** tab:
- **Name** *(required)*: title of the comment
- **Description** *(required)*: content of the comment

In the helpdesk interface, users can only delete their own comments.

### Idea lifecycle

| Status | Value | Color |
|---|---|---|
| New | 1 | Blue (`#2d98b1`) |
| In Study | 2 | Yellow (`#D1A712`) |
| In Progress | 3 | Green (`#4DAA77`) |
| Closed | 4 | Orange (`#d5703b`) |

The status can be changed from the idea's detail page (central interface, UPDATE right required).

### Associating an idea with a ticket

If the `plugin_ideabox_open_ticket` right is enabled on the profile, ideas appear in the linked items selector when creating a ticket (field **Items associable to a ticket**).

---

## Notifications

The plugin automatically creates the following notifications at install time:

| Event | Description |
|---|---|
| `new` | A new idea was submitted |
| `update` | An idea was updated |
| `delete` | An idea was deleted |
| `newcomment` | A new comment was posted on an idea |
| `updatecomment` | A comment was updated |
| `deletecomment` | A comment was deleted |

Notification templates can be configured under **Configuration → Notifications**.  
Available template variables: `##ideabox.url##`, `##ideabox.name##`, `##ideabox.comment##`, `##ideabox.entity##`, and loop variables `##FOREACHcomments##`.

---

## Database schema

| Table | Description |
|---|---|
| `glpi_plugin_ideabox_ideaboxes` | Ideas (name, description, author, date, status, entity) |
| `glpi_plugin_ideabox_comments` | Comments attached to ideas |
| `glpi_plugin_ideabox_votes` | Votes (one per user per idea) |
| `glpi_plugin_ideabox_configs` | Plugin configuration (title, description) |
| `glpi_plugin_ideabox_configtranslations` | Per-language translations of the configuration |

---

## Integrations

### Service Catalog plugin

When the **servicecatalog** plugin is active, Ideabox integrates into the service catalog via the `servicecatalog` hook. The standard helpdesk menu entry is then replaced by the catalog interface.

### Data Injection plugin

Ideas can be bulk-imported using the **datainjection** plugin (type `GlpiPlugin\Ideabox\Ideabox`, internal code `4900`).

---

## Uninstallation

In **Configuration → Plugins**, deactivate then uninstall **Ideabox**. The following tables are dropped:

- `glpi_plugin_ideabox_ideaboxes`
- `glpi_plugin_ideabox_comments`
- `glpi_plugin_ideabox_votes`
- `glpi_plugin_ideabox_configs`
- `glpi_plugin_ideabox_configtranslations`

Associated notifications, notification templates, and display preferences are also removed.

---

## Useful links

- [GitHub repository](https://github.com/InfotelGLPI/ideabox)
- [Report a bug](https://github.com/InfotelGLPI/ideabox/issues)
- [Contribute translations](https://explore.transifex.com/infotelGLPI/GLPI_ideabox/)
- [Infotel GLPI blog](https://blogglpi.infotel.com)
