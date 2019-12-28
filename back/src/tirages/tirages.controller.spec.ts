import { Test, TestingModule } from '@nestjs/testing';
import { TiragesController } from './tirages.controller';

describe('TiragesController', () => {
  let controller: TiragesController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [TiragesController],
    }).compile();

    controller = module.get<TiragesController>(TiragesController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
