### Users
- [ ] Users have attributes:
    - Username
    - Full Name
    - Email address
    - Location
    - Avatar
- [ ] Users cans register for accounts
    - [ ] Users can register/connect with Patreon
    - [ ] Users can register/connect with Twitch
- [ ] Users have Roles & Permissions
    - Guest (unauthenticated)
    - User
    - VIP
    - Moderator
    - Admin
 - [ ] Users may be modified by self & Admins
 - [ ] Users may be deleted by self & Admins
 - [ ] Users may be temporary or permanently banned by Admins
 - Relationships:
    - [ ] Belong to one Role
    - [ ] Have many Threads
    - [ ] Have many Replies
    
### Threads
- [ ] Threads have attributes:
    - Title
    - Body
    - Creator (User)
    - Timestamps
- [ ] Anyone can view Threads
- [ ] Authenticated users can create Threads
- [ ] The creator, Moderators and Admins can modify Threads
- [ ] The creator, Moderators and Admins can delete Threads
- Relationships:
    - [ ] Have many Replies
    - [ ] Belong to one Channel
    - [ ] Belong to one User

### Replies
- [ ] Replies have attributes:
    - Body
    - Creator
    - Timestamps
- [ ] Anyone can view replies
- [ ] Authenticated users can create Replies
- [ ] The creator, Moderators and Admins can modify Replies
- [ ] The creator, Moderators and Admins can delete Replies
- Relationships:
    - [ ] Belong to one Thread
    - [ ] Belong to one User

### Channels
- [ ] Channels have attributes:
    - Name
    - Description
- [ ] Channels have many Threads
- [ ] Channels have Moderators
- [ ] Channels may belong to one parent or have many children (Sub-Channels) ?
- [ ] All Threads and Replies belonging to any Channel may be modified or deleted by Admins
- [ ] Threads and Replies belonging to a specific Channel may be modified or deleted by Admins & Moderators of that Channels
- Relationships:
    - [ ] Belong to one parent (Channel; optional)
    - [ ] Have one child (Channel; optional)
    - [ ] Have many Threads
    - [ ] Have many Moderators
