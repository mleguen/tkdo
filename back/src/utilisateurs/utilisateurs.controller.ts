import { Controller, UseGuards, Get, Param } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import * as moment from 'moment';
import { GetTiragesUtilisateurDTO } from './dto/get-tirages-utilisateur.dto';
import { GetTirageUtilisateurDTO } from './dto/get-tirage-utilisateur.dto';

@UseGuards(AuthGuard('jwt'))
@Controller('utilisateurs')
export class UtilisateursController {
  // TODO : DroitGuard(Droit:GET_TIRAGES_PARTICIPANT) - rejet si l'utilisateur n'a pas un des droits fournis
  // TODO : IdUtilisateurParamGuard() - rejet si l'id utilisateur n'est pas le même que le param id
  @Get('/:idUtilisateur/tirages')
  getTiragesUtilisateur(@Param('idUtilisateur') idUtilisateur: string): GetTiragesUtilisateurDTO {
    return [
      {
        id: 0,
        titre: "Noël",
        date: moment('25/12', 'DD/MM').format()
      },
      {
        id: 1,
        titre: "Réveillon",
        date: moment('31/12', 'DD/MM').format()
      }
    ];
  }

  // TODO : DroitGuard(Droit:GET_TIRAGES_PARTICIPANT) - rejet si l'utilisateur n'a pas un des droits fournis
  // TODO : IdUtilisateurParamGuard() - rejet si l'id utilisateur n'est pas le même que le param id
  @Get('/:idUtilisateur/tirages/:idTirage')
  getTirageUtilisateur(@Param('idUtilisateur') idUtilisateur: string, @Param('idTirage') idTirage: string): GetTirageUtilisateurDTO {
    // TODO : rejet si l'utilisateur n'est pas un des participants du tirage
    return {
      id: 0,
      titre: "Noël",
      date: moment('25/12', 'DD/MM').format(),
      participants: [
        {
          id: 0,
          nom: "Alice"
        },
        {
          id: 1,
          nom: "Bob"
        },
        {
          id: 2,
          nom: "Charlie"
        },
        {
          id: 3,
          nom: "David"
        },
        {
          id: 4,
          nom: "Eve"
        }
      ]
    };
  }
}
