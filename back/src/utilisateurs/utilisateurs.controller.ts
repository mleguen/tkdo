import { Controller, UseGuards, Get, Param } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import * as moment from 'moment';
import { Droit } from '../auth/droit.decorator';
import { DroitsGuard } from '../auth/droits.guard';
import { GetTiragesUtilisateurDTO } from './dto/get-tirages-utilisateur.dto';
import { GetTirageUtilisateurDTO } from './dto/get-tirage-utilisateur.dto';
import { PortHabilitations } from '../../../domaine';

@Controller('utilisateurs')
@UseGuards(AuthGuard('jwt'), DroitsGuard)
export class UtilisateursController {
  // TODO : IdUtilisateurParamGuard() - rejet si l'id utilisateur n'est pas le même que le param id
  @Get('/:idUtilisateur/tirages')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
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

  // TODO : IdUtilisateurParamGuard() - rejet si l'id utilisateur n'est pas le même que le param id
  @Get('/:idUtilisateur/tirages/:idTirage')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
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
