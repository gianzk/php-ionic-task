import { Component, OnInit } from '@angular/core';
import { Router, RouterLink, RouterLinkActive } from '@angular/router';
import {
  IonSplitPane,
  IonMenu,
  IonContent,
  IonList,
  IonListHeader,
  IonNote,
  IonMenuToggle,
  IonItem,
  IonIcon,
  IonLabel,
  IonRouterOutlet,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonAvatar
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  checkboxOutline,
  checkboxSharp,
  logOutOutline,
  logOutSharp,
  personOutline,
  personSharp
} from 'ionicons/icons';
import { AuthService } from '../../services/auth.service';
import { UserResponse } from '../../services/interfaces/user.interface';

@Component({
  selector: 'app-menu-layout',
  templateUrl: './menu-layout.component.html',
  styleUrls: ['./menu-layout.component.scss'],
  imports: [
    RouterLink,
    RouterLinkActive,
    IonSplitPane,
    IonMenu,
    IonContent,
    IonList,
    IonListHeader,
    IonNote,
    IonMenuToggle,
    IonItem,
    IonIcon,
    IonLabel,
    IonRouterOutlet,
    IonHeader,
    IonToolbar,
    IonTitle,
    IonAvatar
  ],
})
export class MenuLayoutComponent implements OnInit {
  public appPages = [
    { title: 'Tasks', url: '/menu/task', icon: 'checkbox' },
  ];

  currentUser: UserResponse | null = null;

  constructor(
    private authService: AuthService,
    private router: Router
  ) {
    addIcons({
      checkboxOutline,
      checkboxSharp,
      logOutOutline,
      logOutSharp,
      personOutline,
      personSharp
    });
  }

  ngOnInit() {
    this.currentUser = this.authService.getCurrentUser();
  }

  logout() {
    this.authService.logout();
    this.router.navigate(['/login']);
  }
}
