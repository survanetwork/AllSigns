# AllSigns
Turn signs into world teleport and command signs.

![](https://poggit.pmmp.io/ci.badge/survanetwork/AllSigns/AllSigns)

[Get the latest AllSigns artifacts (PHAR file) here](https://poggit.pmmp.io/ci/survanetwork/AllSigns/AllSigns)

## Creating signs
You can create world signs to teleport the player to a world or command signs which are running a specific command when the player touches it.

### World
To create a world sign which teleports the player to a specific world when he/she is touching it and is showing how many players are in that world, just create a sign like this and touch it.

![](http://i.imgur.com/UbEQBJE.png)

So write the sign like this:
1. world
2. The name of the world (e.g. lobby)
3. Description of the world.  
4. Nothing (Leave blank)

### Command
To create a command sign which is executes a specific command when he/she touches it, just create a sign like this and touch it.

![](http://i.imgur.com/1EqidAN.png)

Write the sign like this:
1. command
2. Description of the world.  
3. First part of the command
4. Second part of the command  

If the sign looks like this, it'll execute the command "help".  
3. help 
4. Nothing (Leave blank)

If the sign looks like this, it'll also execute the command "help".  
3. he  
4. lp  

## Config

```yaml
# Sign commands and text
world: "world" # When you create a world teleport sign, you need to write that in the first line
worldtext: "§9World" # This will be written in the first line when you created the sign
players: "players" # This is showing the players count of the world, like 7 players

command: "command" # When you create a command sign, you need to write that in the first line
commandtext: "§aCommand" # This will be written in the first line when you created the sign

# Messages
noworld: "§cWorld does not exist" # Message which is sent to the player when a world does not exist
error: "§cError" # Text which is shown on the sign at the players count when the world does not exist
```

## License & Credits
[![Creative Commons License](https://i.creativecommons.org/l/by-sa/4.0/88x31.png)](http://creativecommons.org/licenses/by-sa/4.0/)

You are free to copy, redistribute, change or expand our work, but you must give credits share it under the same license.
[AllSigns](https://github.com/survanetwork/AllSigns) by [surva network](https://github.com/survanetwork) is licensed under a [Creative Commons Attribution-ShareAlike 4.0 International License](http://creativecommons.org/licenses/by-sa/4.0/).
