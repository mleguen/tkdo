import { Controller, UseGuards, Get, Param } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import * as moment from 'moment';
import { PortHabilitations } from '../../../domaine';
import { Droit } from '../auth/droit.decorator';
import { DroitsGuard } from '../auth/droits.guard';
import { IdUtilisateurGuard } from '../auth/id-utilisateur.guard';
import { ParamIdUtilisateur } from '../auth/param-id-utilisateur.decorator';
import { TirageDTO } from './dto/tirage.dto';
import { TirageResumeDTO } from './dto/tirage-resume.dto';

@Controller('utilisateurs')
@UseGuards(AuthGuard('jwt'), DroitsGuard, IdUtilisateurGuard)
export class UtilisateursController {

  @Get('/:idUtilisateur/tirages')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
  @ParamIdUtilisateur()
  getTiragesUtilisateur(@Param('idUtilisateur') idUtilisateur: string): TirageResumeDTO[] {
    return [
      {
        id: 1,
        titre: "Noël",
        date: moment('25/12', 'DD/MM').format()
      },
      {
        id: 2,
        titre: "Réveillon",
        date: moment('31/12', 'DD/MM').format()
      }
    ];
  }

  @Get('/:idUtilisateur/tirages/:idTirage')
  @Droit(PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT)
  @ParamIdUtilisateur()
  getTirageUtilisateur(@Param('idUtilisateur') idUtilisateur: string, @Param('idTirage') idTirage: string): TirageDTO {
    // TODO : rejet si l'utilisateur n'est pas un des participants du tirage
    return {
      id: 1,
      titre: "Noël",
      date: moment('25/12', 'DD/MM').format(),
      participants: [
        {
          id: 1,
          nom: "Alice"
        },
        {
          id: 2,
          nom: "Bob"
        },
        {
          id: 3,
          nom: "Charlie"
        },
        {
          id: 4,
          nom: "David"
        },
        {
          id: 5,
          nom: "Eve"
        }
      ]
    };
  }
}
