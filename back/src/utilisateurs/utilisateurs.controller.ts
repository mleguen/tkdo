import { Controller, UseGuards, Get, Param } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import * as moment from 'moment';
import { PortHabilitations } from '../../../domaine';
import { Droit } from '../auth/droit.decorator';
import { DroitsGuard } from '../auth/droits.guard';
import { IdUtilisateurGuard } from '../auth/id-utilisateur.guard';
import { ParamIdUtilisateur } from '../auth/param-id-utilisateur.decorator';
import { GetTiragesUtilisateurDTO } from './dto/get-tirages-utilisateur.dto';
import { GetTirageUtilisateurDTO } from './dto/get-tirage-utilisateur.dto';

@Controller('utilisateurs')
@UseGuards(AuthGuard('jwt'), DroitsGuard, IdUtilisateurGuard)
export class UtilisateursController {
  @Get('/:idUtilisateur/tirages')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
  @ParamIdUtilisateur()
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

  @Get('/:idUtilisateur/tirages/:idTirage')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
  @ParamIdUtilisateur()
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
