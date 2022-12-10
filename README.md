# Campaign Generator-PHP
Campaign Generator ported to PHP for webpage-integration

Intended for open-ended use in constructing dynamic narratives based on conflicting factions, individuals, and groups. Completed Version 1 will function as such:
- user-chosen and controlled faction presented with a variety of actionable options, allowing the user to choose where to conduct a mission.
- Each mission allows the allocation of capability points, which roughly correspond to the resources that one assigns to the chosen mission, which affects mission success rate.
- Resources are supplied by the locations / objectives that a given faction holds. The higher-level locations/objectives yield more resources

# SimulaeNode

The SimulaeNode class is a php port of SimulaeObject notation, a object structure designed to codify any entity, regardless of corporeality (whether or not it physically exists).

## Structure:

	id
The node 'id' is a unique string identifier that is used as a key for fast-access and reference for other nodes, optimizing Scalability. Hash-key lookups are significantly faster than indexed arrays, which is optimal for large-datasets like complex worlds. 
	
	nodetype 
- a string identifier codifying what category of entity the node is structured as. 
- Identifiers:
	- FAC -> Faction.
	- POI -> Person-Of-Interest.
    - PTY -> Party. a non-faction collection of individuals, which can be generic or specific, depending on membership specifics. Technically unecessary in a finished product as it would be better to 
    - LOC -> Location. A place, area, room, or collection of rooms (commonly known as buildings or structures).
    - OBJ -> Object. Any non-living entity.

Now you may ask, well if the SimulaeNode object structure is designed to codify any type of entity, regardless of category, why would you add nodetype to recategorize them all? 
Well, quite simply, people have difficulty designing systems in which things are the same, but also different. The point of SimulaeNode is that reality bending concepts like "picking up a room, and putting it in your pocket" or ""

	references 
String values

	attributes
numeric values, integer and decimal

	relations
opinion of other related nodes on several factors

- Policy          : ideological differences
- Reputation      : both positive and negative reputation scores
- Two values allows for 2 dimensional opinions (Inspired by the reputation system from Fallout:New Vegas):
   - High positive rep.  -> Good / Liked / Loved
   - High negative rep.  -> Bad / Disliked / Hated
   - Both high reps.     -> Chaotic / Unsure
- Disposition     : A more discrete codification of the relationship between the two nodes
	- neutral     | this node is non-hostile to other node
	- friendly    | this node is friendly to other node
	- hostile     | this node is hostile to other node
	- affiliated  | same group/faction
	- master      | other node is subservient under this node
	- servant     | other node has mastership over this node
	- captor      | this node is detained by node
	- captured    | this node has detained node
- Interactions    : number of interactions between the two nodes

.

	checks
	
boolean (true/false) values
	
- policies    : collection of ideological opinions/beliefs and weights
	- The beliefs are accompanied by weights, which signify the importance of that belief to the entity. The higher the value (closer to 1) the more important the belief is to the entity.

.
	
	abilities 
available actions of this node

- structure TBD


# Planned Features and Mechanics

- Webpage UI/UX
	- prototyped with bootstrap
	- P5.js, JQuery, React.js?
- Antagonistic AI-controlled nodes
	- Faction nodes will deploy resources to accomplish tasks
	- POI and PTY nodes will act autonomously (Interacting, travelling, performing tasks, etc.)
- NPC conversations / Interractions
	- Recieve notifications and directives from Faction NPCs
	- Talk about NPC opinions about events
- Events and effects that make sense.
 	- Successful Surveil / Investigate tasks will reveal (if any) a random node previously unknown to the actor node.

