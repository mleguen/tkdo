import { Test, TestingModule } from '@nestjs/testing';
import { UtilisateursController } from './utilisateurs.controller';

describe('Utilisateurs Controller', () => {
  let controller: UtilisateursController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [UtilisateursController],
    }).compile();

    controller = module.get<UtilisateursController>(UtilisateursController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
