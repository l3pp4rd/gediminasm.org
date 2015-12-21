---
date: "2013-03-03T20:00:00+02:00"
Description: "Why to use window manager instead of desktop and why it should be DWM, what tools fit together"
Section: posts
Slug: dwm-window-manager
Title: "DWM window manager for linux"
---

What are window managers and why you should try it. If any of the following
points ever concerned you, read further.

- You do not need "all that stuff you never use" but your desktop allocates resources to it.
- You wish to have a lightweight, solid, stable environment, just to do the work?
- You want to build your own environment and know what is happening on your system.

<!--more-->

**Last update date: 2013-03-03**

## What is a window manager

You can read about in [wikipedia](http://en.wikipedia.org/wiki/Window_manager) and compare to [desktop
manager/environment](http://en.wikipedia.org/wiki/Desktop_environment). In short, window manager aka **WM** should be
a light layer over **Xorg** to provide a way to control/display windows, handle mouse and keyboard events. Usually these
tend to go further and implement menus, status bars and so on..

I'm a linux user over five years now and sure I used windows, desktop environments like KDE, Gnome, Ubuntu unity. For me
personally, switching to linux, was one of those things, when you never turn your eyes back. The main reason why - is
open source, when it is possible to take a glimpse into the source code, it ensures that there won't be a spyware on
your environment, unless you yourself let it in. Being aware of your environment makes you feel confident.

## So why window manager?

- When you gain experience in linux, you shortly notice that, mostly you have a **shell** and a **web browser** windows opened.
- Next, you are tired to reach your mouse with a hand from keyboard, especially to navigate application menus or switch
away from your shell.
- Then suddenly, why do you need these loads of software to manage some settings, which in general does not provide you
enough and you still dig into those complicated configuration files without any common syntax shared.
- Linux never was the paradise for those who are afraid of shell and I would not like it another way.

## DWM

A window manager like [dwm](http://dwm.suckless.org/) can make it all go away and never bother you again. It is the most
lightweight and solid implementation I have tried. The reason I chose it, was also because this **WM** inspired others
like **Xmonad, awesome**. I have tried xmonad, but to be honest I'm not familiar with haskel and 240 mb of libraries it
requires to install if you want to modify some behavior. There is also a popular one **openbox** but it limits you
through configuration files.

**DWM** comes in pure **C** source code around 2000 lines of code, which can be modified and recompiled on your own
fork. Though it has never failed me so far. It does not come with menu or status bar implementations, you have to do it
yourself. It does not matter whether you do it in C directly or use some tools like: **dzen2** for status bar
and **dmenu** for application menu, by the way it is widely used in xmonad and other wm implementations.

## How it looks

For instance my status bar is implemented using shell script and dzen2 and it looks like:

![DWM status bar image](/img/posts/dwm_status.png)

It does not have any information I do not need. By the way, systray containing skype, is not a part of this status bar.
The source code of the status bar [is here](https://github.com/l3pp4rd/statusbar).

The whole screen looks like:

![DWM tmux shell screen](/img/posts/dwm_screen.png)

This is a tiled tmux urxvt shell (left) and chrome (right) screen. Mouse is not used at all. Theres usually few desktops
you have with windows either floating or in fullscreen mode. If you prefer tiled windows its also an usual choice,
but I prefer fullscreen.

## How it feels

I never thought I can get rid of mouse usage so much. Thanks to **Vimium** on chrome or **vimperator** on firefox too.
Also it feels trully lightweight, there is nothing to compare with a desktop environment, plus it is easy to hack it the
way you may want.

The application menu aka **dmenu** is also very convenient and simple, it also can be used for different purposes.
Triggered by a key shortcut it filters all executables in user defined $PATH, though on typing any key it filters
matches instantly. My [forked version](https://github.com/l3pp4rd/dmenu) has also a patch to topmost the mostly used
ones and lookup $PATH executables to act as application menu.

My fork of [dwm](https://github.com/l3pp4rd/dwm) has also few patches for systray integration and XFT fonts. It is also
made so it could be restarted and recompiled, without losing all active windows. Usually its the same as **.dotfiles**
you have to work on those to get it the way you like it.

Like I said before, ViM and DWM are those things which you never turn your back to. Go on and try it. If DWM does not
work for you, try **i3** or **Xmonad**

