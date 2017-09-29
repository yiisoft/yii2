# Backwards Compatibility

We're strictly not breaking backwards compatibility in patch releases such as `2.x.y.Z` and trying to avoid had to fix
backwards incompatible changes in minor releases such as `2.x.Y`.

Check [Yii Versioning](versions.md) to learn about version numbering. 

## Usage

### Interfaces

Use case | BC?
---------|----
Type hint with the interface | Yes
Call the interface method | Yes
**Implement the interface and...** |
Implement method | Yes
Add argument to method implemented | Yes
Add default value to an argument | Yes

### Classes

Use case | BC?
---------|----
Type hint with the class | Yes
Create a new instance | Yes
Extend the class | Yes
Access a public property | Yes
Call a public method | Yes
**Extend the class and...** |
Access a protected property	| Yes
Call a protected method	| Yes
Override a public property | Yes
Override a protected property | Yes
Override a public method | Yes
Override a protected method | Yes
Add a new property | No
Add a new method | No
Add an argument to an overridden method	| Yes
Add a default value to an argument | Yes
Call a private method (via Reflection) | No
Access a private property (via Reflection) | No


## Development

### Changing interfaces

Type of change | BC?
---------------|----
Remove | No
Change name or namespace | No
Add parent interface | Yes if no new methods are added
Remove parent interface | No
**Interface methods** | 
Add method | No
Remove method | No
Change name | No
Move to parent interface | Yes
Add argument without a default value | No
Add argument with a default value | No
Remove argument | Yes (only last ones)
Add default value to an argument | No
Remove default value of an argument | No
Add type hint to an argument | No
Remove type hint of an argument | No
Change argument type | No
Change return type | No
**Constants** |	 
Add constant | Yes
Remove constant | No
Change value of a constant | Yes except objects that are likely to be serialized. Mandatory to document in UPGRADE.md.

### Classes

Type of change | BC?
---------------|----
Remove | No
Make final | No
Make abstract | No
Change name or namespace | No
Change parent class | Yes but original parent class must remain an ancestor of the class.
Add interface | Yes
Remove interface | No
**Public Properties** | 
Add public property | Yes
Remove public property | No
Reduce visibility | No
Move to parent class | Yes
**Protected Properties** | 	 
Add protected property | Yes
Remove protected property | No
Reduce visibility | No
Move to parent class | Yes
**Private Properties** | 
Add private property | Yes
Remove private property | Yes
**Constructors** | 
Remove constructor | No
Reduce visibility of a public constructor | No
Reduce visibility of a protected constructor | No
Move to parent class | Yes
**Public Methods** |
Add public method | Yes
Remove public method | No
Change name | No
Reduce visibility | No
Move to parent class | Yes
Add argument without a default value | No
Add argument with a default value | No
Remove arguments | Yes, only last ones
Add default value to an argument | No
Remove default value of an argument | No
Add type hint to an argument | No
Remove type hint of an argument | No
Change argument type | No
Change return type | No
**Protected Methods** | 	 
Add protected method | Yes
Remove protected method | No
Change name | No
Reduce visibility | No
Move to parent class | Yes
Add argument without a default value | No
Add argument with a default value | No
Remove arguments | Yes, only last ones
Add default value to an argument | No
Remove default value of an argument | No
Add type hint to an argument | No
Remove type hint of an argument | No
Change argument type | No
Change return type | No
**Private Methods** | 	 
Add private method | Yes
Remove private method | Yes
Change name | Yes
Add argument without a default value | Yes
Add argument with a default value | Yes
Remove argument | Yes
Add default value to an argument | Yes
Remove default value of an argument | Yes
Add type hint to an argument | Yes
Remove type hint of an argument | Yes
Change argument type | Yes
Change return type | Yes
**Static Methods** | 
Turn non static into static | No
Turn static into non static | No
**Constants** | 	 
Add constant | Yes
Remove constant | No
Change value of a constant | Yes except objects that are likely to be serialized. Mandatory to document in UPGRADE.md.