# MineShop

MineShop plugin for Pocketmine, this plugin functions to sell rare items in MineShop, and can be purchased with ore

**SellItems Tutorial:**
Just hold items in your hand and then run the command `/mineshop sell` to sell them.

## ✈️ Commands

| Command            | Description       | Permission                     |
| ------------------ | ----------------- | ------------------------------ |
| `/mineshop`        | Open MineShop GUI | `mineshop.command`             |
| `/mineshop sell`   | Open Sell UI      | `mineshop.command.sell` (OP)   |
| `/mineshop unsell` | Open Unsell GUI   | `mineshop.command.unsell` (OP) |

## 🖼️ Preview

**MineShop**
<p align="left">
  <img src="https://github.com/angga7togk/MineShop/blob/main/img/gui.png?raw=true" width="30%">
  <img src="https://github.com/angga7togk/MineShop/blob/main/img/gui_with.png?raw=true" width="30%">
</p>

**Unsell Mode Menu**
<p align="left">
  <img src="https://github.com/angga7togk/MineShop/blob/main/img/gui_unsell.png?raw=true" width="30%">
</p>

**Sell Item UI**
<p align="left">
  <img src="https://github.com/angga7togk/MineShop/blob/main/img/ui_sell.png?raw=true" width="70%">
</p>

## 💻 Custom GUI
Here I have provided an example json for the custom GUI<br>

**1. Find Folder GUI:**
look for the gui folder in the MineShop plugin data plugin.<br>
**2. Create your GUI:**
create your gui schema with the following example `custom.json`, free file name!<br>
**3. Please look at the title `Default GUI`**<br>
**4. Change GUI Schema:** open config.yml, then change the gui schema you have created.
```yaml
# you can create new gui schema in folder gui/
gui-schema: 'default' # input filename without extension
```

<br>
<br>

The following is an example for the `custom.json` that you created earlier

**Default GUI**
```json
{
  "name": "&lMine Shop",
  "type": "DOUBLE_CHEST",
  "max_slot": 13, // max slot for item selling
  "slots": [
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "mineshop:slot",
    "mineshop:slot",
    "mineshop:slot",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "mineshop:slot",
    "mineshop:slot",
    "mineshop:slot",
    "mineshop:slot",
    "mineshop:slot",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "mineshop:slot",
    "mineshop:slot",
    "mineshop:slot",
    "mineshop:slot",
    "mineshop:slot",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "minecraft:vines",
    "mineshop:previous",
    "minecraft:vines",
    "mineshop:info",
    "minecraft:vines",
    "mineshop:next",
    "minecraft:vines",
    "minecraft:vines"
  ]
}
```
## 📜 Credits

Icon by [Flaticon](https://www.flaticon.com/)