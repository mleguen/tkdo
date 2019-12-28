import { Test, TestingModule } from '@nestjs/testing';
import { TiragesService } from './tirages.service';

describe('TiragesService', () => {
  let service: TiragesService;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [TiragesService],
    }).compile();

    service = module.get<TiragesService>(TiragesService);
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});
